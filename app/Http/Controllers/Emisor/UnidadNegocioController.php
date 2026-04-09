<?php

namespace App\Http\Controllers\Emisor;

use App\Http\Controllers\Controller;
use App\Models\UnidadNegocio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnidadNegocioController extends Controller
{
    public function index(): View
    {
        $emisor = auth()->user()->emisor;

        $unidades = UnidadNegocio::where('emisor_id', $emisor->id)
            ->withCount(['establecimientos', 'users'])
            ->orderBy('nombre')
            ->get();

        return view('emisor.unidades-negocio.index', compact('unidades'));
    }

    public function create(): View
    {
        return view('emisor.unidades-negocio.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $emisor = auth()->user()->emisor;

        $validated = $request->validate([
            'nombre' => 'required|string|max:200',
            'activo' => 'boolean',
        ]);

        $validated['emisor_id'] = $emisor->id;

        UnidadNegocio::create($validated);

        return redirect()->route('emisor.configuracion.unidades-negocio.index')
            ->with('success', 'Linea de negocio creada correctamente.');
    }

    public function edit(UnidadNegocio $unidades_negocio): View
    {
        $this->autorizarAcceso($unidades_negocio);

        $unidad = $unidades_negocio;

        return view('emisor.unidades-negocio.edit', compact('unidad'));
    }

    public function update(Request $request, UnidadNegocio $unidades_negocio): RedirectResponse
    {
        $this->autorizarAcceso($unidades_negocio);

        $validated = $request->validate([
            'nombre' => 'required|string|max:200',
            'activo' => 'boolean',
        ]);

        $unidades_negocio->update($validated);

        return redirect()->route('emisor.configuracion.unidades-negocio.index')
            ->with('success', 'Linea de negocio actualizada correctamente.');
    }

    private function autorizarAcceso(UnidadNegocio $unidad): void
    {
        $user = auth()->user();
        if (!$user->esAdmin() && $unidad->emisor_id !== $user->emisor_id) {
            abort(403);
        }
    }
}
