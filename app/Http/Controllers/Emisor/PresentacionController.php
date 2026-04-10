<?php

namespace App\Http\Controllers\Emisor;

use App\Http\Controllers\Controller;
use App\Models\Presentacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PresentacionController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = Presentacion::where('emisor_id', $user->emisor_id);

        if ($request->filled('buscar')) {
            $query->where('nombre', 'like', "%{$request->buscar}%");
        }

        $presentaciones = $query->withCount('productos')->orderBy('nombre')->paginate(50);

        return view('emisor.farmacia.presentaciones.index', compact('presentaciones'));
    }

    public function create(): View
    {
        return view('emisor.farmacia.presentaciones.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'activo' => 'boolean',
        ]);

        $validated['emisor_id'] = $user->emisor_id;
        $validated['activo'] = $request->boolean('activo', true);

        Presentacion::create($validated);

        return redirect()->route('emisor.farmacia.presentaciones.index')
            ->with('success', 'Presentacion creada correctamente.');
    }

    public function edit(Presentacion $presentacion): View
    {
        $this->autorizarAcceso($presentacion);

        return view('emisor.farmacia.presentaciones.edit', compact('presentacion'));
    }

    public function update(Request $request, Presentacion $presentacion): RedirectResponse
    {
        $this->autorizarAcceso($presentacion);

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'activo' => 'boolean',
        ]);

        $validated['activo'] = $request->boolean('activo', true);
        $presentacion->update($validated);

        return redirect()->route('emisor.farmacia.presentaciones.index')
            ->with('success', 'Presentacion actualizada correctamente.');
    }

    public function destroy(Presentacion $presentacion): RedirectResponse
    {
        $this->autorizarAcceso($presentacion);

        if ($presentacion->productos()->exists()) {
            return redirect()->route('emisor.farmacia.presentaciones.index')
                ->with('error', 'No se puede eliminar una presentacion con productos asociados.');
        }

        $presentacion->delete();

        return redirect()->route('emisor.farmacia.presentaciones.index')
            ->with('success', 'Presentacion eliminada.');
    }

    private function autorizarAcceso(Presentacion $presentacion): void
    {
        $user = auth()->user();
        if (!$user->esAdmin() && $presentacion->emisor_id !== $user->emisor_id) {
            abort(403);
        }
    }
}
