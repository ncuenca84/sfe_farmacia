<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        $planes = Plan::withCount('suscripciones')->paginate(50);
        return view('admin.planes.index', compact('planes'));
    }

    public function create(): View
    {
        return view('admin.planes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'cant_comprobante' => 'required|integer|min:0',
            'tipo_periodo' => 'required|in:MENSUAL,ANUAL,DIAS',
            'dias' => 'nullable|required_if:tipo_periodo,DIAS|integer|min:1',
            'precio' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string',
        ]);

        Plan::create($validated);

        return redirect()->route('admin.planes.index')
            ->with('success', 'Plan creado correctamente.');
    }

    public function edit(Plan $plan): View
    {
        return view('admin.planes.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'cant_comprobante' => 'required|integer|min:0',
            'tipo_periodo' => 'required|in:MENSUAL,ANUAL,DIAS',
            'dias' => 'nullable|required_if:tipo_periodo,DIAS|integer|min:1',
            'precio' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        $plan->update($validated);

        return redirect()->route('admin.planes.index')
            ->with('success', 'Plan actualizado correctamente.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        $plan->update(['activo' => false]);
        return redirect()->route('admin.planes.index')
            ->with('success', 'Plan desactivado correctamente.');
    }
}
