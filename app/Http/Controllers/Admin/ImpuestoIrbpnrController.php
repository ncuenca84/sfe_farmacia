<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImpuestoIrbpnr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ImpuestoIrbpnrController extends Controller
{
    public function index(): View
    {
        $impuestos = ImpuestoIrbpnr::orderBy('codigo_porcentaje')->paginate(50);
        return view('admin.impuestos.irbpnr.index', compact('impuestos'));
    }

    public function create(): View
    {
        return view('admin.impuestos.irbpnr.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'codigo_porcentaje' => 'required|string|max:4',
            'nombre' => 'required|string|max:255',
            'tarifa' => 'required|numeric|min:0',
        ]);

        ImpuestoIrbpnr::create($validated);
        return redirect()->route('admin.impuesto-irbpnrs.index')->with('success', 'IRBPNR creado.');
    }

    public function edit(ImpuestoIrbpnr $impuestoIrbpnr): View
    {
        return view('admin.impuestos.irbpnr.edit', ['impuesto' => $impuestoIrbpnr]);
    }

    public function update(Request $request, ImpuestoIrbpnr $impuestoIrbpnr): RedirectResponse
    {
        $validated = $request->validate([
            'codigo_porcentaje' => 'required|string|max:4',
            'nombre' => 'required|string|max:255',
            'tarifa' => 'required|numeric|min:0',
            'activo' => 'boolean',
        ]);

        $impuestoIrbpnr->update($validated);
        return redirect()->route('admin.impuesto-irbpnrs.index')->with('success', 'IRBPNR actualizado.');
    }

    public function destroy(ImpuestoIrbpnr $impuestoIrbpnr): RedirectResponse
    {
        $impuestoIrbpnr->update(['activo' => false]);
        return redirect()->route('admin.impuesto-irbpnrs.index')->with('success', 'IRBPNR desactivado.');
    }
}
