<?php

namespace App\Console\Commands;

use App\Models\Emisor;
use App\Models\Inventario;
use App\Models\Producto;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AlertasFarmaciaCommand extends Command
{
    protected $signature = 'farmacia:alertas {--dias=30 : Dias para alerta de vencimiento}';
    protected $description = 'Envía alertas de stock bajo y productos próximos a vencer por email';

    public function handle(): int
    {
        $dias = (int) $this->option('dias');

        $emisores = Emisor::where('activo', true)
            ->whereNotNull('mail_from_address')
            ->get();

        foreach ($emisores as $emisor) {
            $alertas = $this->obtenerAlertas($emisor, $dias);

            if (empty($alertas['stock_bajo']) && empty($alertas['proximos_vencer']) && empty($alertas['vencidos'])) {
                continue;
            }

            $this->enviarAlerta($emisor, $alertas, $dias);
            $this->info("Alerta enviada a: {$emisor->razon_social}");
        }

        $this->info('Proceso de alertas completado.');
        return self::SUCCESS;
    }

    private function obtenerAlertas(Emisor $emisor, int $dias): array
    {
        $stockBajo = DB::table('inventarios')
            ->join('productos', 'inventarios.producto_id', '=', 'productos.id')
            ->join('establecimientos', 'inventarios.establecimiento_id', '=', 'establecimientos.id')
            ->where('inventarios.emisor_id', $emisor->id)
            ->where('inventarios.stock_minimo', '>', 0)
            ->whereColumn('inventarios.stock_actual', '<=', 'inventarios.stock_minimo')
            ->select('productos.nombre', 'establecimientos.nombre as establecimiento',
                'inventarios.stock_actual', 'inventarios.stock_minimo')
            ->orderBy('productos.nombre')
            ->get();

        $proximosVencer = Producto::where('emisor_id', $emisor->id)
            ->where('activo', true)
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '>=', now())
            ->where('fecha_vencimiento', '<=', now()->addDays($dias))
            ->select('nombre', 'numero_lote', 'fecha_vencimiento')
            ->orderBy('fecha_vencimiento')
            ->get();

        $vencidos = Producto::where('emisor_id', $emisor->id)
            ->where('activo', true)
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<', now())
            ->select('nombre', 'numero_lote', 'fecha_vencimiento')
            ->orderBy('fecha_vencimiento')
            ->limit(20)
            ->get();

        return [
            'stock_bajo' => $stockBajo,
            'proximos_vencer' => $proximosVencer,
            'vencidos' => $vencidos,
        ];
    }

    private function enviarAlerta(Emisor $emisor, array $alertas, int $dias): void
    {
        $destinatario = $emisor->mail_from_address;
        if (!$destinatario) return;

        $lineas = ["Alertas de Farmacia - {$emisor->razon_social}", ""];

        if ($alertas['vencidos']->isNotEmpty()) {
            $lineas[] = "== PRODUCTOS VENCIDOS ({$alertas['vencidos']->count()}) ==";
            foreach ($alertas['vencidos'] as $p) {
                $lineas[] = "- {$p->nombre} | Lote: {$p->numero_lote} | Vencido: {$p->fecha_vencimiento->format('d/m/Y')}";
            }
            $lineas[] = "";
        }

        if ($alertas['proximos_vencer']->isNotEmpty()) {
            $lineas[] = "== PROXIMOS A VENCER EN {$dias} DIAS ({$alertas['proximos_vencer']->count()}) ==";
            foreach ($alertas['proximos_vencer'] as $p) {
                $lineas[] = "- {$p->nombre} | Lote: {$p->numero_lote} | Vence: {$p->fecha_vencimiento->format('d/m/Y')}";
            }
            $lineas[] = "";
        }

        if ($alertas['stock_bajo']->isNotEmpty()) {
            $lineas[] = "== STOCK BAJO ({$alertas['stock_bajo']->count()}) ==";
            foreach ($alertas['stock_bajo'] as $p) {
                $lineas[] = "- {$p->nombre} | {$p->establecimiento} | Stock: {$p->stock_actual} (Min: {$p->stock_minimo})";
            }
        }

        $cuerpo = implode("\n", $lineas);

        try {
            Mail::raw($cuerpo, function ($msg) use ($destinatario, $emisor) {
                $msg->to($destinatario)
                    ->subject("Alertas Farmacia - {$emisor->razon_social} - " . now()->format('d/m/Y'));
            });
        } catch (\Throwable $e) {
            $this->error("Error enviando a {$emisor->razon_social}: {$e->getMessage()}");
        }
    }
}
