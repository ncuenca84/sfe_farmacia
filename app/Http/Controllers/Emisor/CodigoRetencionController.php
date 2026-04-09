<?php

namespace App\Http\Controllers\Emisor;

use App\Http\Controllers\Controller;
use App\Models\CodigoRetencion;
use Illuminate\Http\JsonResponse;
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
        return view('emisor.codigos-retencion.index', compact('codigos'));
    }

    public function buscar(Request $request): JsonResponse
    {
        $query = CodigoRetencion::activos();

        if ($request->filled('tipo')) {
            $tipoMap = ['1' => 'RENTA', '2' => 'IVA', '6' => 'ISD'];
            $tipo = $tipoMap[$request->tipo] ?? $request->tipo;
            $query->where('tipo', $tipo);
        }
        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('codigo', 'like', "%{$request->q}%")
                  ->orWhere('descripcion', 'like', "%{$request->q}%");
            });
        }

        $codigos = $query->orderBy('tipo')->orderBy('codigo')->paginate(8);

        return response()->json($codigos);
    }

    public function create(): View
    {
        return view('emisor.codigos-retencion.create');
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
        return redirect()->route('emisor.codigos-retencion.index')->with('success', 'Código de retención creado.');
    }

    public function edit(CodigoRetencion $codigoRetencion): View
    {
        return view('emisor.codigos-retencion.edit', ['codigo' => $codigoRetencion]);
    }

    public function update(Request $request, CodigoRetencion $codigoRetencion): RedirectResponse
    {
        $validated = $request->validate([
            'tipo' => 'required|in:RENTA,IVA,ISD',
            'codigo' => 'required|string|max:10',
            'descripcion' => 'required|string',
            'porcentaje' => 'required|numeric|min:0',
            'activo' => 'boolean',
        ]);

        $codigoRetencion->update($validated);
        return redirect()->route('emisor.codigos-retencion.index')->with('success', 'Código actualizado.');
    }
}
