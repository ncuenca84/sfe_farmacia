<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Emisor;
use App\Models\Establecimiento;
use App\Models\PtoEmision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PtoEmisionController extends Controller
{
    public function index(Request $request): View
    {
        $query = PtoEmision::with(['establecimiento.emisor']);

        if ($request->filled('emisor_id')) {
            $query->whereHas('establecimiento', fn ($q) => $q->where('emisor_id', $request->emisor_id));
        }

        if ($request->filled('establecimiento_id')) {
            $query->where('establecimiento_id', $request->establecimiento_id);
        }

        $ptosEmision = $query->orderBy('establecimiento_id')->orderBy('codigo')->paginate(50);
        $emisores = Emisor::where('activo', true)->orderBy('razon_social')->get();

        return view('admin.puntos-emision.index', compact('ptosEmision', 'emisores'));
    }

    public function create(Request $request): View
    {
        $emisores = Emisor::where('activo', true)->orderBy('razon_social')->get();
        $establecimientos = collect();

        if ($request->filled('emisor_id')) {
            $establecimientos = Establecimiento::where('emisor_id', $request->emisor_id)->where('activo', true)->orderBy('codigo')->get();
        }

        return view('admin.puntos-emision.create', compact('emisores', 'establecimientos'));
    }

    public function store(Request $request): RedirectResponse
    {
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

        $validated['activo'] = $request->boolean('activo', true);

        PtoEmision::create($validated);

        $establecimiento = Establecimiento::find($validated['establecimiento_id']);

        return redirect()->route('admin.puntos-emision.index', ['emisor_id' => $establecimiento->emisor_id])
            ->with('success', 'Punto de emisión creado correctamente.');
    }

    public function edit(PtoEmision $puntosEmision): View
    {
        $puntosEmision->load('establecimiento.emisor');
        $emisores = Emisor::where('activo', true)->orderBy('razon_social')->get();
        $establecimientos = Establecimiento::where('emisor_id', $puntosEmision->establecimiento->emisor_id)
            ->where('activo', true)->orderBy('codigo')->get();

        return view('admin.puntos-emision.edit', compact('puntosEmision', 'emisores', 'establecimientos'));
    }

    public function update(Request $request, PtoEmision $puntosEmision): RedirectResponse
    {
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

        $validated['activo'] = $request->boolean('activo');

        $puntosEmision->update($validated);

        return redirect()->route('admin.puntos-emision.index', ['emisor_id' => $puntosEmision->establecimiento->emisor_id])
            ->with('success', 'Punto de emisión actualizado correctamente.');
    }

    public function destroy(PtoEmision $puntosEmision): RedirectResponse
    {
        $emisorId = $puntosEmision->establecimiento->emisor_id;
        $puntosEmision->delete();

        return redirect()->route('admin.puntos-emision.index', ['emisor_id' => $emisorId])
            ->with('success', 'Punto de emisión eliminado.');
    }
}
