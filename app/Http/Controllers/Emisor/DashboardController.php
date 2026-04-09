<?php

namespace App\Http\Controllers\Emisor;

use App\Http\Controllers\Controller;
use App\Models\Factura;
use App\Models\NotaCredito;
use App\Models\NotaDebito;
use App\Models\Retencion;
use App\Models\Guia;
use App\Models\LiquidacionCompra;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $emisor = $user->emisor;

        if ($user->esAdmin() && !$emisor) {
            return view('emisor.dashboard', $this->datosVacios());
        }

        $suscripcion = $emisor->suscripcionActiva;

        // IDs de establecimientos filtrados por unidad de negocio del usuario
        $establecimientoIds = $user->establecimientosActivos()->pluck('id');

        // Scope para filtrar comprobantes por establecimiento del usuario
        $scopeEstab = function (Builder $query) use ($establecimientoIds) {
            $query->whereIn('establecimiento_id', $establecimientoIds);
        };

        $mesActual = now()->month;
        $anioActual = now()->year;

        // --- Contadores del mes actual ---
        $facturasMes = $emisor->facturas()->where($scopeEstab)
            ->whereMonth('fecha_emision', $mesActual)
            ->whereYear('fecha_emision', $anioActual)
            ->count();

        $ncMes = $emisor->notaCreditos()->where($scopeEstab)
            ->whereMonth('fecha_emision', $mesActual)
            ->whereYear('fecha_emision', $anioActual)
            ->count();

        $ndMes = $emisor->notaDebitos()->where($scopeEstab)
            ->whereMonth('fecha_emision', $mesActual)
            ->whereYear('fecha_emision', $anioActual)
            ->count();

        $retencionesMes = $emisor->retenciones()->where($scopeEstab)
            ->whereMonth('fecha_emision', $mesActual)
            ->whereYear('fecha_emision', $anioActual)
            ->count();

        $guiasMes = $emisor->guias()->where($scopeEstab)
            ->whereMonth('fecha_emision', $mesActual)
            ->whereYear('fecha_emision', $anioActual)
            ->count();

        // --- Ventas del mes (importe_total facturas) ---
        $ventasMes = $emisor->facturas()->where($scopeEstab)
            ->whereMonth('fecha_emision', $mesActual)
            ->whereYear('fecha_emision', $anioActual)
            ->sum('importe_total');

        // --- Tendencia mensual últimos 6 meses (facturas) ---
        $tendenciaMensual = $emisor->facturas()->where($scopeEstab)
            ->where('fecha_emision', '>=', now()->subMonths(5)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(fecha_emision, '%Y-%m') as mes"),
                DB::raw('COUNT(*) as total'),
                DB::raw('COALESCE(SUM(importe_total), 0) as ventas')
            )
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        // Rellenar meses faltantes
        $mesesLabels = [];
        $mesesCantidad = [];
        $mesesVentas = [];
        for ($i = 5; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $clave = $fecha->format('Y-m');
            $mesesLabels[] = $fecha->translatedFormat('M Y');
            $dato = $tendenciaMensual->firstWhere('mes', $clave);
            $mesesCantidad[] = $dato ? $dato->total : 0;
            $mesesVentas[] = $dato ? round((float) $dato->ventas, 2) : 0;
        }

        // --- Estado de comprobantes del mes (facturas) ---
        $estadosFacturas = $emisor->facturas()->where($scopeEstab)
            ->whereMonth('fecha_emision', $mesActual)
            ->whereYear('fecha_emision', $anioActual)
            ->select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();

        // --- Top 5 clientes del mes por ventas ---
        $topClientes = $emisor->facturas()->where($scopeEstab)
            ->whereMonth('fecha_emision', $mesActual)
            ->whereYear('fecha_emision', $anioActual)
            ->join('clientes', 'facturas.cliente_id', '=', 'clientes.id')
            ->select(
                'clientes.razon_social',
                DB::raw('COUNT(*) as num_facturas'),
                DB::raw('COALESCE(SUM(facturas.importe_total), 0) as total_ventas')
            )
            ->groupBy('clientes.id', 'clientes.razon_social')
            ->orderByDesc('total_ventas')
            ->limit(5)
            ->get();

        // --- Distribución por tipo de comprobante del mes ---
        $distribucionTipos = [
            'Facturas' => $facturasMes,
            'N. Crédito' => $ncMes,
            'N. Débito' => $ndMes,
            'Retenciones' => $retencionesMes,
            'Guías' => $guiasMes,
        ];

        return view('emisor.dashboard', compact(
            'suscripcion',
            'facturasMes', 'ncMes', 'ndMes', 'retencionesMes', 'guiasMes',
            'ventasMes',
            'mesesLabels', 'mesesCantidad', 'mesesVentas',
            'estadosFacturas',
            'topClientes',
            'distribucionTipos'
        ));
    }

    private function datosVacios(): array
    {
        return [
            'suscripcion' => null,
            'facturasMes' => 0,
            'ncMes' => 0,
            'ndMes' => 0,
            'retencionesMes' => 0,
            'guiasMes' => 0,
            'ventasMes' => 0,
            'mesesLabels' => [],
            'mesesCantidad' => [],
            'mesesVentas' => [],
            'estadosFacturas' => [],
            'topClientes' => collect(),
            'distribucionTipos' => [],
        ];
    }
}
