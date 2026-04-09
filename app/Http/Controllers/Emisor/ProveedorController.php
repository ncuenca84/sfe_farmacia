<?php

namespace App\Http\Controllers\Emisor;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProveedorController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = Proveedor::where('emisor_id', $user->emisor_id);

        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->buscar}%")
                    ->orWhere('identificacion', 'like', "%{$request->buscar}%");
            });
        }

        $proveedores = $query->withCount('productos')->orderBy('nombre')->paginate(50);

        return view('emisor.farmacia.proveedores.index', compact('proveedores'));
    }

    public function create(): View
    {
        return view('emisor.farmacia.proveedores.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'identificacion' => 'nullable|string|max:20',
            'nombre' => 'required|string|max:300',
            'direccion' => 'nullable|string|max:300',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
            'contacto' => 'nullable|string|max:200',
            'activo' => 'boolean',
        ]);

        $validated['emisor_id'] = $user->emisor_id;
        $validated['activo'] = $request->boolean('activo', true);

        Proveedor::create($validated);

        return redirect()->route('emisor.farmacia.proveedores.index')
            ->with('success', 'Proveedor creado correctamente.');
    }

    public function edit(Proveedor $proveedor): View
    {
        $this->autorizarAcceso($proveedor);

        return view('emisor.farmacia.proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, Proveedor $proveedor): RedirectResponse
    {
        $this->autorizarAcceso($proveedor);

        $validated = $request->validate([
            'identificacion' => 'nullable|string|max:20',
            'nombre' => 'required|string|max:300',
            'direccion' => 'nullable|string|max:300',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
            'contacto' => 'nullable|string|max:200',
            'activo' => 'boolean',
        ]);

        $validated['activo'] = $request->boolean('activo', true);

        $proveedor->update($validated);

        return redirect()->route('emisor.farmacia.proveedores.index')
            ->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Proveedor $proveedor): RedirectResponse
    {
        $this->autorizarAcceso($proveedor);

        if ($proveedor->productos()->exists()) {
            return redirect()->route('emisor.farmacia.proveedores.index')
                ->with('error', 'No se puede eliminar un proveedor con productos asociados.');
        }

        $proveedor->delete();

        return redirect()->route('emisor.farmacia.proveedores.index')
            ->with('success', 'Proveedor eliminado.');
    }

    private function autorizarAcceso(Proveedor $proveedor): void
    {
        $user = auth()->user();
        if (!$user->esAdmin() && $proveedor->emisor_id !== $user->emisor_id) {
            abort(403);
        }
    }
}
