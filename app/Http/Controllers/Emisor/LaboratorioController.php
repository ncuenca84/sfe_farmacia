<?php

namespace App\Http\Controllers\Emisor;

use App\Http\Controllers\Controller;
use App\Models\Laboratorio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaboratorioController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = Laboratorio::where('emisor_id', $user->emisor_id);

        if ($request->filled('buscar')) {
            $query->where('nombre', 'like', "%{$request->buscar}%");
        }

        $laboratorios = $query->withCount('productos')->orderBy('nombre')->paginate(50);

        return view('emisor.farmacia.laboratorios.index', compact('laboratorios'));
    }

    public function create(): View
    {
        return view('emisor.farmacia.laboratorios.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'nombre' => 'required|string|max:200',
            'pais' => 'nullable|string|max:100',
            'activo' => 'boolean',
        ]);

        $validated['emisor_id'] = $user->emisor_id;
        $validated['activo'] = $request->boolean('activo', true);

        Laboratorio::create($validated);

        return redirect()->route('emisor.farmacia.laboratorios.index')
            ->with('success', 'Laboratorio creado correctamente.');
    }

    public function edit(Laboratorio $laboratorio): View
    {
        $this->autorizarAcceso($laboratorio);

        return view('emisor.farmacia.laboratorios.edit', compact('laboratorio'));
    }

    public function update(Request $request, Laboratorio $laboratorio): RedirectResponse
    {
        $this->autorizarAcceso($laboratorio);

        $validated = $request->validate([
            'nombre' => 'required|string|max:200',
            'pais' => 'nullable|string|max:100',
            'activo' => 'boolean',
        ]);

        $validated['activo'] = $request->boolean('activo', true);
        $laboratorio->update($validated);

        return redirect()->route('emisor.farmacia.laboratorios.index')
            ->with('success', 'Laboratorio actualizado correctamente.');
    }

    public function destroy(Laboratorio $laboratorio): RedirectResponse
    {
        $this->autorizarAcceso($laboratorio);

        if ($laboratorio->productos()->exists()) {
            return redirect()->route('emisor.farmacia.laboratorios.index')
                ->with('error', 'No se puede eliminar un laboratorio con productos asociados.');
        }

        $laboratorio->delete();

        return redirect()->route('emisor.farmacia.laboratorios.index')
            ->with('success', 'Laboratorio eliminado.');
    }

    private function autorizarAcceso(Laboratorio $laboratorio): void
    {
        $user = auth()->user();
        if (!$user->esAdmin() && $laboratorio->emisor_id !== $user->emisor_id) {
            abort(403);
        }
    }
}
