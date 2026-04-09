<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImpuestoIce;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ImpuestoIceController extends Controller
{
    public function index(): View
    {
        $impuestos = ImpuestoIce::orderBy('codigo_porcentaje')->paginate(50);
        return view('admin.impuestos.ice.index', compact('impuestos'));
    }

    public function create(): View
    {
        return view('admin.impuestos.ice.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'codigo_porcentaje' => 'required|string|max:4',
            'nombre' => 'required|string|max:255',
            'tarifa' => 'required|numeric|min:0',
        ]);

        ImpuestoIce::create($validated);
        return redirect()->route('admin.impuesto-ices.index')->with('success', 'ICE creado.');
    }

    public function edit(ImpuestoIce $impuestoIce): View
    {
        return view('admin.impuestos.ice.edit', ['impuesto' => $impuestoIce]);
    }

    public function update(Request $request, ImpuestoIce $impuestoIce): RedirectResponse
    {
        $validated = $request->validate([
            'codigo_porcentaje' => 'required|string|max:4',
            'nombre' => 'required|string|max:255',
            'tarifa' => 'required|numeric|min:0',
            'activo' => 'boolean',
        ]);

        $impuestoIce->update($validated);
        return redirect()->route('admin.impuesto-ices.index')->with('success', 'ICE actualizado.');
    }

    public function destroy(ImpuestoIce $impuestoIce): RedirectResponse
    {
        $impuestoIce->update(['activo' => false]);
        return redirect()->route('admin.impuesto-ices.index')->with('success', 'ICE desactivado.');
    }
}
