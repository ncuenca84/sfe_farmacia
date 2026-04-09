<?php

namespace App\Services;

use App\Enums\TipoMovimiento;
use App\Models\Emisor;
use App\Models\Inventario;
use App\Models\MovimientoInventario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InventarioService
{
    /**
     * Registra una salida de stock (ej: factura autorizada).
     */
    public function registrarSalida(
        Emisor $emisor,
        int $productoId,
        int $establecimientoId,
        float $cantidad,
        float $costoUnitario = 0,
        ?string $descripcion = null,
        ?Model $referencia = null,
    ): MovimientoInventario {
        return $this->registrarMovimiento(
            emisor: $emisor,
            productoId: $productoId,
            establecimientoId: $establecimientoId,
            tipo: TipoMovimiento::SALIDA,
            cantidad: $cantidad,
            costoUnitario: $costoUnitario,
            descripcion: $descripcion,
            referencia: $referencia,
        );
    }

    /**
     * Registra una entrada de stock (ej: nota de crédito autorizada, compra).
     */
    public function registrarEntrada(
        Emisor $emisor,
        int $productoId,
        int $establecimientoId,
        float $cantidad,
        float $costoUnitario = 0,
        ?string $descripcion = null,
        ?Model $referencia = null,
    ): MovimientoInventario {
        return $this->registrarMovimiento(
            emisor: $emisor,
            productoId: $productoId,
            establecimientoId: $establecimientoId,
            tipo: TipoMovimiento::ENTRADA,
            cantidad: $cantidad,
            costoUnitario: $costoUnitario,
            descripcion: $descripcion,
            referencia: $referencia,
        );
    }

    /**
     * Registra un ajuste manual de inventario (positivo o negativo).
     */
    public function registrarAjuste(
        Emisor $emisor,
        int $productoId,
        int $establecimientoId,
        float $cantidad,
        float $costoUnitario = 0,
        ?string $descripcion = null,
    ): MovimientoInventario {
        return $this->registrarMovimiento(
            emisor: $emisor,
            productoId: $productoId,
            establecimientoId: $establecimientoId,
            tipo: TipoMovimiento::AJUSTE,
            cantidad: $cantidad,
            costoUnitario: $costoUnitario,
            descripcion: $descripcion,
        );
    }

    /**
     * Procesa movimientos de inventario al autorizar una factura.
     */
    public function procesarFacturaAutorizada(Model $factura): void
    {
        $emisor = $factura->emisor;
        $establecimiento = $factura->establecimiento;
        if (!$establecimiento || !$establecimiento->maneja_inventario) {
            return;
        }

        // Evitar movimientos duplicados
        if ($this->yaRegistrado($factura)) {
            return;
        }

        $factura->loadMissing('detalles');

        foreach ($factura->detalles as $detalle) {
            if (!$detalle->codigo_principal) {
                continue;
            }

            $producto = $emisor->productos()
                ->where('codigo_principal', $detalle->codigo_principal)
                ->first();

            if (!$producto) {
                continue;
            }

            $this->registrarSalida(
                emisor: $emisor,
                productoId: $producto->id,
                establecimientoId: $factura->establecimiento_id,
                cantidad: (float) $detalle->cantidad,
                costoUnitario: (float) $detalle->precio_unitario,
                descripcion: "Factura {$factura->numero_completo}",
                referencia: $factura,
            );
        }
    }

    /**
     * Procesa movimientos de inventario al autorizar una nota de crédito (devuelve stock).
     */
    public function procesarNotaCreditoAutorizada(Model $notaCredito): void
    {
        $emisor = $notaCredito->emisor;
        $establecimiento = $notaCredito->establecimiento;
        if (!$establecimiento || !$establecimiento->maneja_inventario) {
            return;
        }

        if ($this->yaRegistrado($notaCredito)) {
            return;
        }

        $notaCredito->loadMissing('detalles');

        foreach ($notaCredito->detalles as $detalle) {
            if (!$detalle->codigo_principal) {
                continue;
            }

            $producto = $emisor->productos()
                ->where('codigo_principal', $detalle->codigo_principal)
                ->first();

            if (!$producto) {
                continue;
            }

            $this->registrarEntrada(
                emisor: $emisor,
                productoId: $producto->id,
                establecimientoId: $notaCredito->establecimiento_id,
                cantidad: (float) $detalle->cantidad,
                costoUnitario: (float) $detalle->precio_unitario,
                descripcion: "Nota de Crédito {$notaCredito->numero_completo}",
                referencia: $notaCredito,
            );
        }
    }

    /**
     * Procesa movimientos de inventario al autorizar una liquidación de compra (entrada de stock).
     */
    public function procesarLiquidacionAutorizada(Model $liquidacion): void
    {
        $emisor = $liquidacion->emisor;
        $establecimiento = $liquidacion->establecimiento;
        if (!$establecimiento || !$establecimiento->maneja_inventario) {
            return;
        }

        if ($this->yaRegistrado($liquidacion)) {
            return;
        }

        $liquidacion->loadMissing('detalles');

        foreach ($liquidacion->detalles as $detalle) {
            if (!$detalle->codigo_principal) {
                continue;
            }

            $producto = $emisor->productos()
                ->where('codigo_principal', $detalle->codigo_principal)
                ->first();

            if (!$producto) {
                continue;
            }

            $this->registrarEntrada(
                emisor: $emisor,
                productoId: $producto->id,
                establecimientoId: $liquidacion->establecimiento_id,
                cantidad: (float) $detalle->cantidad,
                costoUnitario: (float) $detalle->precio_unitario,
                descripcion: "Liquidación {$liquidacion->numero_completo}",
                referencia: $liquidacion,
            );
        }
    }

    /**
     * Verifica si ya se registraron movimientos para este documento (evita duplicados).
     */
    private function yaRegistrado(Model $documento): bool
    {
        return MovimientoInventario::where('referencia_type', get_class($documento))
            ->where('referencia_id', $documento->id)
            ->exists();
    }

    /**
     * Core: registra un movimiento y actualiza stock con lock para concurrencia.
     */
    private function registrarMovimiento(
        Emisor $emisor,
        int $productoId,
        int $establecimientoId,
        TipoMovimiento $tipo,
        float $cantidad,
        float $costoUnitario = 0,
        ?string $descripcion = null,
        ?Model $referencia = null,
    ): MovimientoInventario {
        return DB::transaction(function () use ($emisor, $productoId, $establecimientoId, $tipo, $cantidad, $costoUnitario, $descripcion, $referencia) {
            // Obtener o crear inventario con lock
            $inventario = Inventario::where('producto_id', $productoId)
                ->where('establecimiento_id', $establecimientoId)
                ->lockForUpdate()
                ->first();

            if (!$inventario) {
                $inventario = Inventario::create([
                    'emisor_id' => $emisor->id,
                    'producto_id' => $productoId,
                    'establecimiento_id' => $establecimientoId,
                    'stock_actual' => 0,
                    'stock_minimo' => 0,
                    'costo_promedio' => 0,
                ]);
                // Re-lock after create
                $inventario = Inventario::where('id', $inventario->id)->lockForUpdate()->first();
            }

            $stockAnterior = (float) $inventario->stock_actual;

            // Calcular nuevo stock
            $nuevoStock = match ($tipo) {
                TipoMovimiento::ENTRADA => $stockAnterior + $cantidad,
                TipoMovimiento::SALIDA => $stockAnterior - $cantidad,
                TipoMovimiento::AJUSTE => $stockAnterior + $cantidad, // cantidad puede ser negativa
                TipoMovimiento::TRANSFERENCIA => $stockAnterior - $cantidad,
            };

            // Actualizar costo promedio ponderado en entradas y ajustes positivos
            if (in_array($tipo, [TipoMovimiento::ENTRADA, TipoMovimiento::AJUSTE]) && $cantidad > 0 && $costoUnitario > 0) {
                $costoAnteriorTotal = $stockAnterior * (float) $inventario->costo_promedio;
                $costoNuevoTotal = $cantidad * $costoUnitario;
                $nuevoStockPositivo = max($nuevoStock, 0.0001); // Evitar división por cero
                $inventario->costo_promedio = ($costoAnteriorTotal + $costoNuevoTotal) / $nuevoStockPositivo;
            }

            $inventario->stock_actual = $nuevoStock;
            $inventario->save();

            $costoTotal = $cantidad * $costoUnitario;

            return MovimientoInventario::create([
                'emisor_id' => $emisor->id,
                'inventario_id' => $inventario->id,
                'producto_id' => $productoId,
                'establecimiento_id' => $establecimientoId,
                'tipo' => $tipo,
                'cantidad' => $cantidad,
                'costo_unitario' => $costoUnitario,
                'costo_total' => $costoTotal,
                'stock_resultante' => $nuevoStock,
                'referencia_type' => $referencia ? get_class($referencia) : null,
                'referencia_id' => $referencia?->id,
                'descripcion' => $descripcion,
                'user_id' => auth()->id(),
            ]);
        });
    }
}
