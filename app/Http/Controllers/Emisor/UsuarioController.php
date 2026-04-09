<?php

namespace App\Http\Controllers\Emisor;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\UnidadNegocio;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $emisor = $user->emisor;

        $query = User::with('unidadNegocio')->where('emisor_id', $emisor->id);

        if ($user->unidad_negocio_id) {
            $query->where('unidad_negocio_id', $user->unidad_negocio_id);
        }

        $usuarios = $query->orderBy('nombre')->paginate(50);

        $tieneMultiplesUnidades = UnidadNegocio::where('emisor_id', $emisor->id)->count() > 1;

        return view('emisor.usuarios.index', compact('usuarios', 'tieneMultiplesUnidades'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $emisor = $user->emisor;
        $roles = Role::whereIn('nombre', ['ROLE_EMISOR_ADMIN', 'ROLE_EMISOR'])->get();

        $queryUnidades = UnidadNegocio::where('emisor_id', $emisor->id)->where('activo', true);
        if ($user->unidad_negocio_id) {
            $queryUnidades->where('id', $user->unidad_negocio_id);
        }
        $unidadesNegocio = $queryUnidades->orderBy('nombre')->get();

        return view('emisor.usuarios.create', compact('roles', 'unidadesNegocio'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $emisor = $user->emisor;

        $validated = $request->validate([
            'username' => 'required|string|max:100|unique:users',
            'nombre' => 'required|string|max:150',
            'apellido' => 'required|string|max:150',
            'email' => 'required|string|email|max:200|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'rol_id' => 'required|exists:roles,id',
            'unidad_negocio_id' => 'nullable|exists:unidades_negocio,id',
        ]);

        $validated['emisor_id'] = $emisor->id;
        $validated['password'] = Hash::make($validated['password']);

        // Forzar la unidad de negocio del usuario logueado si tiene una asignada
        if ($user->unidad_negocio_id) {
            $validated['unidad_negocio_id'] = $user->unidad_negocio_id;
        }

        User::create($validated);

        return redirect()->route('emisor.configuracion.usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function show(User $usuario): View
    {
        $this->autorizarAcceso($usuario);

        return view('emisor.usuarios.show', compact('usuario'));
    }

    public function edit(User $usuario): View
    {
        $this->autorizarAcceso($usuario);
        $user = auth()->user();
        $emisor = $user->emisor;
        $roles = Role::whereIn('nombre', ['ROLE_EMISOR_ADMIN', 'ROLE_EMISOR'])->get();

        $queryUnidades = UnidadNegocio::where('emisor_id', $emisor->id)->where('activo', true);
        if ($user->unidad_negocio_id) {
            $queryUnidades->where('id', $user->unidad_negocio_id);
        }
        $unidadesNegocio = $queryUnidades->orderBy('nombre')->get();

        return view('emisor.usuarios.edit', compact('usuario', 'roles', 'unidadesNegocio'));
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $this->autorizarAcceso($usuario);

        $validated = $request->validate([
            'username' => 'required|string|max:100|unique:users,username,' . $usuario->id,
            'nombre' => 'required|string|max:150',
            'apellido' => 'required|string|max:150',
            'email' => 'required|string|email|max:200|unique:users,email,' . $usuario->id,
            'password' => 'nullable|string|min:8|confirmed',
            'rol_id' => 'required|exists:roles,id',
            'unidad_negocio_id' => 'nullable|exists:unidades_negocio,id',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $usuario->update($validated);

        return redirect()->route('emisor.configuracion.usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $usuario): RedirectResponse
    {
        $this->autorizarAcceso($usuario);

        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puede eliminar su propio usuario.');
        }

        $usuario->delete();

        return redirect()->route('emisor.configuracion.usuarios.index')
            ->with('success', 'Usuario eliminado.');
    }

    private function autorizarAcceso(User $usuario): void
    {
        $user = auth()->user();
        if (!$user->esAdmin() && $usuario->emisor_id !== $user->emisor_id) {
            abort(403);
        }
        // Restringir acceso a usuarios de otra linea de negocio
        if ($user->unidad_negocio_id && $usuario->unidad_negocio_id !== $user->unidad_negocio_id) {
            abort(403);
        }
    }
}
