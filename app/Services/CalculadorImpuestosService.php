<?php

namespace App\Services;

use App\Models\ImpuestoIva;

class CalculadorImpuestosService
{
    /**
     * Calcula los impuestos de un detalle de comprobante.
     *
     * @return array{base_imponible: float, iva: float, ice: float, irbpnr: float, total: float, impuestos: array}
     */
    public function calcularDetalle(
        float $cantidad,
        float $precioUnitario,
        float $descuento,
        int $impuestoIvaId,
        ?float $valorIce = null,
        ?float $valorIrbpnr = null
    ): array {
        // Usar 6 decimales en cálculos intermedios (requisito SRI),
        // redondear a 2 solo en los resultados finales por línea.
        $subtotal = round($cantidad * $precioUnitario, 6);
        $baseImponible = round($subtotal - $descuento, 2);

        $impuestos = [];

        // IVA
        $iva = ImpuestoIva::find($impuestoIvaId);
        $valorIva = 0;
        if ($iva) {
            $valorIva = round($baseImponible * ($iva->tarifa / 100), 2);
            $impuestos[] = [
                'codigo' => '2', // IVA
                'codigo_porcentaje' => $iva->codigo_porcentaje,
                'tarifa' => $iva->tarifa,
                'base_imponible' => $baseImponible,
                'valor' => $valorIva,
            ];
        }

        // ICE
        $totalIce = $valorIce ?? 0;
        if ($totalIce > 0) {
            $impuestos[] = [
                'codigo' => '3', // ICE
                'codigo_porcentaje' => '',
                'tarifa' => 0,
                'base_imponible' => $baseImponible,
                'valor' => $totalIce,
            ];
        }

        // IRBPNR
        $totalIrbpnr = $valorIrbpnr ?? 0;
        if ($totalIrbpnr > 0) {
            $impuestos[] = [
                'codigo' => '5', // IRBPNR
                'codigo_porcentaje' => '5001',
                'tarifa' => 0.02,
                'base_imponible' => $cantidad,
                'valor' => $totalIrbpnr,
            ];
        }

        return [
            'precio_total_sin_impuesto' => $baseImponible,
            'iva' => $valorIva,
            'ice' => $totalIce,
            'irbpnr' => $totalIrbpnr,
            'total' => round($baseImponible + $valorIva + $totalIce + $totalIrbpnr, 2),
            'impuestos' => $impuestos,
        ];
    }

    /**
     * Totaliza impuestos de un arreglo de detalles ya calculados.
     */
    public function totalizar(array $detallesCalculados): array
    {
        $totalSinImpuestos = 0;
        $totalDescuento = 0;
        $totalIva = 0;
        $totalIce = 0;
        $totalIrbpnr = 0;

        foreach ($detallesCalculados as $detalle) {
            $totalSinImpuestos += $detalle['precio_total_sin_impuesto'];
            $totalDescuento += $detalle['descuento'] ?? 0;
            $totalIva += $detalle['iva'];
            $totalIce += $detalle['ice'];
            $totalIrbpnr += $detalle['irbpnr'];
        }

        $importeTotal = round($totalSinImpuestos + $totalIva + $totalIce + $totalIrbpnr, 2);

        return [
            'total_sin_impuestos' => round($totalSinImpuestos, 2),
            'total_descuento' => round($totalDescuento, 2),
            'total_iva' => round($totalIva, 2),
            'total_ice' => round($totalIce, 2),
            'total_irbpnr' => round($totalIrbpnr, 2),
            'importe_total' => $importeTotal,
        ];
    }
}
