<?php

namespace App\Services;

use App\Models\Lote;
use App\Models\LoteMovimiento;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LoteService
{
    /**
     * Ingresa un nuevo lote (recepción de mercadería).
     */
    public function ingresarLote(
        int $emisorId,
        int $productoId,
        int $establecimientoId,
        string $numeroLote,
        float $cantidad,
        float $costoUnitario = 0,
        ?string $fechaVencimiento = null,
        ?string $fechaIngreso = null,
        ?string $nota = null,
    ): Lote {
        return DB::transaction(function () use ($emisorId, $productoId, $establecimientoId, $numeroLote, $cantidad, $costoUnitario, $fechaVencimiento, $fechaIngreso, $nota) {
            $lote = Lote::create([
                'emisor_id' => $emisorId,
                'producto_id' => $productoId,
                'establecimiento_id' => $establecimientoId,
                'numero_lote' => $numeroLote,
                'fecha_vencimiento' => $fechaVencimiento,
                'cantidad_inicial' => $cantidad,
                'cantidad_actual' => $cantidad,
                'costo_unitario' => $costoUnitario,
                'fecha_ingreso' => $fechaIngreso ?? now()->toDateString(),
                'nota' => $nota,
            ]);

            LoteMovimiento::create([
                'lote_id' => $lote->id,
                'tipo' => 'ENTRADA',
                'cantidad' => $cantidad,
                'cantidad_anterior' => 0,
                'cantidad_posterior' => $cantidad,
                'descripcion' => 'Ingreso inicial de lote',
                'user_id' => auth()->id(),
            ]);

            return $lote;
        });
    }

    /**
     * Consume stock usando FEFO (First Expired First Out).
     * Devuelve array de [lote_id => cantidad_consumida].
     */
    public function consumirFefo(
        int $productoId,
        int $establecimientoId,
        float $cantidad,
        ?string $descripcion = null,
        ?Model $referencia = null,
    ): array {
        return DB::transaction(function () use ($productoId, $establecimientoId, $cantidad, $descripcion, $referencia) {
            $lotes = Lote::where('producto_id', $productoId)
                ->where('establecimiento_id', $establecimientoId)
                ->fefo()
                ->lockForUpdate()
                ->get();

            $restante = $cantidad;
            $consumido = [];

            foreach ($lotes as $lote) {
                if ($restante <= 0) {
                    break;
                }

                $aConsumir = min($restante, (float) $lote->cantidad_actual);
                $anterior = (float) $lote->cantidad_actual;
                $posterior = $anterior - $aConsumir;

                $lote->cantidad_actual = $posterior;
                $lote->save();

                LoteMovimiento::create([
                    'lote_id' => $lote->id,
                    'tipo' => 'SALIDA',
                    'cantidad' => $aConsumir,
                    'cantidad_anterior' => $anterior,
                    'cantidad_posterior' => $posterior,
                    'referencia_type' => $referencia ? get_class($referencia) : null,
                    'referencia_id' => $referencia?->id,
                    'descripcion' => $descripcion,
                    'user_id' => auth()->id(),
                ]);

                $consumido[$lote->id] = $aConsumir;
                $restante -= $aConsumir;
            }

            return $consumido;
        });
    }

    /**
     * Ajuste manual de cantidad en un lote.
     */
    public function ajustarLote(
        Lote $lote,
        float $nuevaCantidad,
        ?string $descripcion = null,
    ): LoteMovimiento {
        return DB::transaction(function () use ($lote, $nuevaCantidad, $descripcion) {
            $lote = Lote::where('id', $lote->id)->lockForUpdate()->first();
            $anterior = (float) $lote->cantidad_actual;
            $diferencia = $nuevaCantidad - $anterior;

            $lote->cantidad_actual = $nuevaCantidad;
            $lote->save();

            return LoteMovimiento::create([
                'lote_id' => $lote->id,
                'tipo' => 'AJUSTE',
                'cantidad' => $diferencia,
                'cantidad_anterior' => $anterior,
                'cantidad_posterior' => $nuevaCantidad,
                'descripcion' => $descripcion ?? 'Ajuste manual',
                'user_id' => auth()->id(),
            ]);
        });
    }

    /**
     * Devuelve stock a lotes (para notas de crédito).
     * Intenta devolver al lote original si se indica, o al más reciente.
     */
    public function devolverStock(
        int $productoId,
        int $establecimientoId,
        float $cantidad,
        ?string $descripcion = null,
        ?Model $referencia = null,
    ): array {
        return DB::transaction(function () use ($productoId, $establecimientoId, $cantidad, $descripcion, $referencia) {
            // Devolver al lote más reciente que aún tenga espacio
            $lotes = Lote::where('producto_id', $productoId)
                ->where('establecimiento_id', $establecimientoId)
                ->where('activo', true)
                ->orderByDesc('fecha_ingreso')
                ->lockForUpdate()
                ->get();

            $restante = $cantidad;
            $devuelto = [];

            foreach ($lotes as $lote) {
                if ($restante <= 0) {
                    break;
                }

                $anterior = (float) $lote->cantidad_actual;
                $aDevolver = $restante; // devolver todo al primer lote disponible
                $posterior = $anterior + $aDevolver;

                $lote->cantidad_actual = $posterior;
                $lote->save();

                LoteMovimiento::create([
                    'lote_id' => $lote->id,
                    'tipo' => 'ENTRADA',
                    'cantidad' => $aDevolver,
                    'cantidad_anterior' => $anterior,
                    'cantidad_posterior' => $posterior,
                    'referencia_type' => $referencia ? get_class($referencia) : null,
                    'referencia_id' => $referencia?->id,
                    'descripcion' => $descripcion,
                    'user_id' => auth()->id(),
                ]);

                $devuelto[$lote->id] = $aDevolver;
                $restante -= $aDevolver;
            }

            return $devuelto;
        });
    }

    /**
     * Stock total por lotes para un producto en un establecimiento.
     */
    public function stockPorLotes(int $productoId, int $establecimientoId): float
    {
        return (float) Lote::where('producto_id', $productoId)
            ->where('establecimiento_id', $establecimientoId)
            ->where('activo', true)
            ->sum('cantidad_actual');
    }
}
