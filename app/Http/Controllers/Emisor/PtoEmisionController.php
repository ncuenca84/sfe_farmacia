<?php

namespace App\Http\Controllers\Emisor;

use App\Http\Controllers\Controller;
use App\Models\PtoEmision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PtoEmisionController extends Controller
{
    public function index(Request $request): View
    {
        $emisor = auth()->user()->emisor;

        $query = PtoEmision::whereHas('establecimiento', fn ($q) => $q->where('emisor_id', $emisor->id))
            ->with(['establecimiento']);

        if (auth()->user()->unidad_negocio_id) {
            $query->whereHas('establecimiento', fn ($q) => $q->where('unidad_negocio_id', auth()->user()->unidad_negocio_id));
        }

        $ptosEmision = $query->orderBy('codigo')->paginate(50);

        return view('emisor.puntos-emision.index', compact('ptosEmision'));
    }

    public function create(): View
    {
        $emisor = auth()->user()->emisor;
        $establecimientos = auth()->user()->establecimientosActivos();

        return view('emisor.puntos-emision.create', compact('establecimientos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $emisor = auth()->user()->emisor;

        $validated = $request->validate([
            'establecimiento_id' => 'required|exists:establecimientos,id',
            'codigo' => 'required|string|max:3',
            'nombre' => 'nullable|string|max:200',
            'activo' => 'boolean',
            'sec_factura' => 'nullable|integer|min:0',
            'sec_nota_credito' => 'nullable|integer|min:0',
            'sec_nota_debito' => 'nullable|integer|min:0',
            'sec_retencion' => 'nullable|integer|min:0',
            'sec_guia' => 'nullable|integer|min:0',
            'sec_liquidacion' => 'nullable|integer|min:0',
            'sec_proforma' => 'nullable|integer|min:0',
        ]);

        // Verificar que el establecimiento pertenece al emisor
        $establecimiento = $emisor->establecimientos()->findOrFail($validated['establecimiento_id']);

        PtoEmision::create($validated);

        return redirect()->route('emisor.configuracion.puntos-emision.index')
            ->with('success', 'Punto de emisión creado correctamente.');
    }

    public function show(PtoEmision $puntosEmision): View
    {
        $this->autorizarAcceso($puntosEmision);
        $puntosEmision->load('establecimiento');

        return view('emisor.puntos-emision.show', compact('puntosEmision'));
    }

    public function edit(PtoEmision $puntosEmision): View
    {
        $this->autorizarAcceso($puntosEmision);
        $emisor = auth()->user()->emisor;
        $establecimientos = auth()->user()->establecimientosActivos();

        return view('emisor.puntos-emision.edit', compact('puntosEmision', 'establecimientos'));
    }

    public function update(Request $request, PtoEmision $puntosEmision): RedirectResponse
    {
        $this->autorizarAcceso($puntosEmision);

        $validated = $request->validate([
            'establecimiento_id' => 'required|exists:establecimientos,id',
            'codigo' => 'required|string|max:3',
            'nombre' => 'nullable|string|max:200',
            'activo' => 'boolean',
            'sec_factura' => 'nullable|integer|min:0',
            'sec_nota_credito' => 'nullable|integer|min:0',
            'sec_nota_debito' => 'nullable|integer|min:0',
            'sec_retencion' => 'nullable|integer|min:0',
            'sec_guia' => 'nullable|integer|min:0',
            'sec_liquidacion' => 'nullable|integer|min:0',
            'sec_proforma' => 'nullable|integer|min:0',
        ]);

        $puntosEmision->update($validated);

        return redirect()->route('emisor.configuracion.puntos-emision.index')
            ->with('success', 'Punto de emisión actualizado correctamente.');
    }

    public function destroy(PtoEmision $puntosEmision): RedirectResponse
    {
        $this->autorizarAcceso($puntosEmision);
        $puntosEmision->delete();

        return redirect()->route('emisor.configuracion.puntos-emision.index')
            ->with('success', 'Punto de emisión eliminado.');
    }

    private function autorizarAcceso(PtoEmision $ptoEmision): void
    {
        $user = auth()->user();
        if (!$user->esAdmin() && $ptoEmision->establecimiento->emisor_id !== $user->emisor_id) {
            abort(403);
        }
    }
}
