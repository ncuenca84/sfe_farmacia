<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\WhmcsConfig;
use App\Models\WhmcsServicio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WhmcsController extends Controller
{
    public function configuracion(): View
    {
        $config = WhmcsConfig::first();
        return view('admin.whmcs.configuracion', compact('config'));
    }

    public function guardarConfiguracion(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'whmcs_url' => 'required|url|max:255',
        ]);

        $config = WhmcsConfig::first();
        if ($config) {
            $config->update($validated);
        } else {
            WhmcsConfig::create(array_merge($validated, [
                'api_key' => Str::random(64),
            ]));
        }

        return back()->with('success', 'Configuración WHMCS guardada.');
    }

    public function generarApiKey(): RedirectResponse
    {
        $config = WhmcsConfig::first();
        if ($config) {
            $config->update(['api_key' => Str::random(64)]);
        }

        return back()->with('success', 'Nueva API Key generada.');
    }

    public function servicios(): View
    {
        $servicios = WhmcsServicio::with('emisor')->paginate(50);
        return view('admin.whmcs.servicios', compact('servicios'));
    }

    public function planes(): View
    {
        $planes = Plan::where('activo', true)->get();
        return view('admin.whmcs.planes', compact('planes'));
    }

    public function mapearPlan(Request $request, Plan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'whmcs_package_id' => 'nullable|integer',
        ]);

        $plan->update($validated);
        return back()->with('success', 'Plan mapeado correctamente.');
    }
}
