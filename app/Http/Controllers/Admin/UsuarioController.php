<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Emisor;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with(['rol', 'emisor']);

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('username', 'like', "%{$buscar}%")
                  ->orWhere('nombre', 'like', "%{$buscar}%")
                  ->orWhere('apellido', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('rol')) {
            $query->whereHas('rol', fn ($q) => $q->where('nombre', $request->rol));
        }

        if ($request->filled('emisor_id')) {
            $query->where('emisor_id', $request->emisor_id);
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->activo);
        }

        $usuarios = $query->orderBy('created_at', 'desc')->paginate(50);
        $roles = Role::all();
        $emisores = Emisor::where('activo', true)->orderBy('razon_social')->get();

        return view('admin.usuarios.index', compact('usuarios', 'roles', 'emisores'));
    }

    public function create(): View
    {
        $roles = Role::all();
        $emisores = Emisor::where('activo', true)->orderBy('razon_social')->get();
        return view('admin.usuarios.create', compact('roles', 'emisores'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => 'required|string|max:100|unique:users,username',
            'nombre' => 'required|string|max:150',
            'apellido' => 'required|string|max:150',
            'email' => 'required|email|max:200',
            'password' => 'required|string|min:8|confirmed',
            'rol_id' => 'required|exists:roles,id',
            'emisor_id' => 'nullable|exists:emisores,id',
            'activo' => 'boolean',
        ]);

        $validated['activo'] = $request->boolean('activo', true);

        User::create($validated);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario): View
    {
        $roles = Role::all();
        $emisores = Emisor::where('activo', true)->orderBy('razon_social')->get();
        return view('admin.usuarios.edit', compact('usuario', 'roles', 'emisores'));
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($usuario->id)],
            'nombre' => 'required|string|max:150',
            'apellido' => 'required|string|max:150',
            'email' => 'required|email|max:200',
            'password' => 'nullable|string|min:8|confirmed',
            'rol_id' => 'required|exists:roles,id',
            'emisor_id' => 'nullable|exists:emisores,id',
            'activo' => 'boolean',
        ]);

        $validated['activo'] = $request->boolean('activo');

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $usuario->update($validated);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $usuario): RedirectResponse
    {
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puede eliminar su propio usuario.');
        }

        $usuario->delete();

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }
}
