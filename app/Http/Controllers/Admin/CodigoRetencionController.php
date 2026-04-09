<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CodigoRetencion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CodigoRetencionController extends Controller
{
    public function index(Request $request): View
    {
        $query = CodigoRetencion::query();

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('codigo', 'like', "%{$request->buscar}%")
                  ->orWhere('descripcion', 'like', "%{$request->buscar}%");
            });
        }

        $codigos = $query->orderBy('tipo')->orderBy('codigo')->paginate(50);
        return view('admin.codigos-retencion.index', compact('codigos'));
    }

    public function create(): View
    {
        return view('admin.codigos-retencion.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tipo' => 'required|in:RENTA,IVA,ISD',
            'codigo' => 'required|string|max:10',
            'descripcion' => 'required|string',
            'porcentaje' => 'required|numeric|min:0',
        ]);

        CodigoRetencion::create($validated);
        return redirect()->route('admin.codigos-retencion.index')->with('success', 'Código de retención creado.');
    }

    public function edit(CodigoRetencion $codigosRetencion): View
    {
        return view('admin.codigos-retencion.edit', ['codigo' => $codigosRetencion]);
    }

    public function update(Request $request, CodigoRetencion $codigosRetencion): RedirectResponse
    {
        $validated = $request->validate([
            'tipo' => 'required|in:RENTA,IVA,ISD',
            'codigo' => 'required|string|max:10',
            'descripcion' => 'required|string',
            'porcentaje' => 'required|numeric|min:0',
            'activo' => 'boolean',
        ]);

        $codigosRetencion->update($validated);
        return redirect()->route('admin.codigos-retencion.index')->with('success', 'Código actualizado.');
    }

    public function destroy(CodigoRetencion $codigosRetencion): RedirectResponse
    {
        $codigosRetencion->update(['activo' => false]);
        return redirect()->route('admin.codigos-retencion.index')->with('success', 'Código desactivado.');
    }
}
