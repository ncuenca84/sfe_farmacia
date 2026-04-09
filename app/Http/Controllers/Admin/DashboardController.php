<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Emisor;
use App\Models\EmisorSuscripcion;
use App\Models\Factura;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $emisoresActivos = Emisor::where('activo', true)->count();
        $emisoresInactivos = Emisor::where('activo', false)->count();

        $suscripcionesVencidas = EmisorSuscripcion::where('estado', 'VENCIDA')
            ->whereHas('emisor', fn ($q) => $q->where('activo', true))
            ->count();

        $suscripcionesPorVencer = EmisorSuscripcion::where('estado', 'ACTIVA')
            ->where('fecha_fin', '<=', now()->addDays(7))
            ->where('fecha_fin', '>=', now())
            ->with(['emisor', 'plan'])
            ->get();

        $comprobantesMes = Factura::whereMonth('fecha_emision', now()->month)
            ->whereYear('fecha_emision', now()->year)
            ->count();

        return view('admin.dashboard', compact(
            'emisoresActivos', 'emisoresInactivos',
            'suscripcionesVencidas', 'suscripcionesPorVencer',
            'comprobantesMes'
        ));
    }
}
