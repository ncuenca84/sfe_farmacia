<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImpuestoIva;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ImpuestoIvaController extends Controller
{
    public function index(): View
    {
        $impuestos = ImpuestoIva::orderBy('codigo_porcentaje')->paginate(50);
        return view('admin.impuestos.iva.index', compact('impuestos'));
    }

    public function create(): View
    {
        return view('admin.impuestos.iva.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'codigo_porcentaje' => 'required|string|max:4',
            'nombre' => 'required|string|max:100',
            'tarifa' => 'required|numeric|min:0',
            'activo' => 'boolean',
            'fecha_vigencia_desde' => 'nullable|date',
            'fecha_vigencia_hasta' => 'nullable|date',
        ]);

        ImpuestoIva::create($validated);
        return redirect()->route('admin.impuesto-ivas.index')->with('success', 'Tarifa IVA creada.');
    }

    public function edit(ImpuestoIva $impuestoIva): View
    {
        return view('admin.impuestos.iva.edit', ['impuesto' => $impuestoIva]);
    }

    public function update(Request $request, ImpuestoIva $impuestoIva): RedirectResponse
    {
        $validated = $request->validate([
            'codigo_porcentaje' => 'required|string|max:4',
            'nombre' => 'required|string|max:100',
            'tarifa' => 'required|numeric|min:0',
            'activo' => 'boolean',
            'fecha_vigencia_desde' => 'nullable|date',
            'fecha_vigencia_hasta' => 'nullable|date',
        ]);

        $impuestoIva->update($validated);
        return redirect()->route('admin.impuesto-ivas.index')->with('success', 'Tarifa IVA actualizada.');
    }

    public function destroy(ImpuestoIva $impuestoIva): RedirectResponse
    {
        $impuestoIva->update(['activo' => false]);
        return redirect()->route('admin.impuesto-ivas.index')->with('success', 'Tarifa IVA desactivada.');
    }
}
