<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Emisor;
use App\Models\Establecimiento;
use App\Models\UnidadNegocio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EstablecimientoController extends Controller
{
    public function index(Request $request): View
    {
        $query = Establecimiento::with(['emisor', 'unidadNegocio', 'ptoEmisiones']);

        if ($request->filled('emisor_id')) {
            $query->where('emisor_id', $request->emisor_id);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('codigo', 'like', "%{$buscar}%")
                  ->orWhere('nombre', 'like', "%{$buscar}%")
                  ->orWhere('direccion', 'like', "%{$buscar}%");
            });
        }

        $establecimientos = $query->orderBy('emisor_id')->orderBy('codigo')->paginate(50);
        $emisores = Emisor::where('activo', true)->orderBy('razon_social')->get();

        return view('admin.establecimientos.index', compact('establecimientos', 'emisores'));
    }

    public function create(): View
    {
        $emisores = Emisor::where('activo', true)->orderBy('razon_social')->get();
        return view('admin.establecimientos.create', compact('emisores'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'emisor_id' => 'required|exists:emisores,id',
            'codigo' => 'required|string|max:3',
            'direccion' => 'required|string|max:300',
            'nombre' => 'nullable|string|max:200',
            'activo' => 'boolean',
            'maneja_inventario' => 'boolean',
        ]);

        $validated['activo'] = $request->boolean('activo', true);
        $validated['maneja_inventario'] = $request->boolean('maneja_inventario');

        Establecimiento::create($validated);

        return redirect()->route('admin.establecimientos.index', ['emisor_id' => $validated['emisor_id']])
            ->with('success', 'Establecimiento creado correctamente.');
    }

    public function edit(Establecimiento $establecimiento): View
    {
        $establecimiento->load('emisor');
        $emisores = Emisor::where('activo', true)->orderBy('razon_social')->get();
        return view('admin.establecimientos.edit', compact('establecimiento', 'emisores'));
    }

    public function update(Request $request, Establecimiento $establecimiento): RedirectResponse
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:3',
            'direccion' => 'required|string|max:300',
            'nombre' => 'nullable|string|max:200',
            'activo' => 'boolean',
            'maneja_inventario' => 'boolean',
        ]);

        $validated['activo'] = $request->boolean('activo');
        $validated['maneja_inventario'] = $request->boolean('maneja_inventario');

        $establecimiento->update($validated);

        return redirect()->route('admin.establecimientos.index', ['emisor_id' => $establecimiento->emisor_id])
            ->with('success', 'Establecimiento actualizado correctamente.');
    }

    public function destroy(Establecimiento $establecimiento): RedirectResponse
    {
        $emisorId = $establecimiento->emisor_id;
        $establecimiento->delete();

        return redirect()->route('admin.establecimientos.index', ['emisor_id' => $emisorId])
            ->with('success', 'Establecimiento eliminado.');
    }
}
