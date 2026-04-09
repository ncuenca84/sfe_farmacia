<?php

namespace App\Services;

use App\Models\Emisor;
use App\Models\Factura;
use App\Models\FacturaDetalle;
use App\Models\FacturaReembolso;
use App\Models\Impuesto;
use App\Models\InfoGuiaRemision;
use App\Models\PtoEmision;
use Illuminate\Support\Facades\DB;

class FacturaService
{
    public function __construct(
        private CalculadorImpuestosService $calculador,
        private SriService $sriService
    ) {}

    public function actualizar(Factura $factura, array $data): Factura
    {
        return DB::transaction(function () use ($factura, $data) {
            $detallesCalculados = [];
            foreach ($data['detalles'] as $det) {
                $calculo = $this->calculador->calcularDetalle(
                    cantidad: (float) $det['cantidad'],
                    precioUnitario: (float) $det['precio_unitario'],
                    descuento: (float) ($det['descuento'] ?? 0),
                    impuestoIvaId: (int) $det['impuesto_iva_id'],
                );
                $detallesCalculados[] = array_merge($det, $calculo);
            }

            $totales = $this->calculador->totalizar($detallesCalculados);

            $factura->update([
                'establecimiento_id' => $data['establecimiento_id'],
                'pto_emision_id' => $data['pto_emision_id'],
                'cliente_id' => $data['cliente_id'],
                'fecha_emision' => $data['fecha_emision'],
                'total_sin_impuestos' => $totales['total_sin_impuestos'],
                'total_descuento' => $totales['total_descuento'],
                'total_iva' => $totales['total_iva'],
                'total_ice' => $totales['total_ice'],
                'total_irbpnr' => $totales['total_irbpnr'],
                'importe_total' => $totales['importe_total'],
                'forma_pago' => $data['forma_pago'] ?? '01',
                'forma_pago_valor' => $totales['importe_total'],
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            // Delete old detalles (impuestos cascade via polymorphic)
            foreach ($factura->detalles as $detalle) {
                $detalle->impuestos()->delete();
                $detalle->delete();
            }

            // Recreate detalles with impuestos
            foreach ($detallesCalculados as $det) {
                $detalle = FacturaDetalle::create([
                    'factura_id' => $factura->id,
                    'codigo_principal' => $det['codigo_principal'] ?? null,
                    'descripcion' => $det['descripcion'],
                    'cantidad' => $det['cantidad'],
                    'precio_unitario' => $det['precio_unitario'],
                    'descuento' => $det['descuento'] ?? 0,
                    'precio_total_sin_impuesto' => $det['precio_total_sin_impuesto'],
                ]);

                foreach ($det['impuestos'] as $imp) {
                    Impuesto::create([
                        'detalle_type' => FacturaDetalle::class,
                        'detalle_id' => $detalle->id,
                        'codigo' => $imp['codigo'],
                        'codigo_porcentaje' => $imp['codigo_porcentaje'],
                        'tarifa' => $imp['tarifa'],
                        'base_imponible' => $imp['base_imponible'],
                        'valor' => $imp['valor'],
                    ]);
                }
            }

            return $factura->fresh();
        });
    }

    /**
     * Crea una factura completa con detalles, impuestos y la envía al SRI.
     */
    public function crear(Emisor $emisor, array $data): Factura
    {
        return DB::transaction(function () use ($emisor, $data) {
            $ptoEmision = PtoEmision::findOrFail($data['pto_emision_id']);
            $secuencial = $ptoEmision->siguienteSecuencial('factura');

            // Calcular impuestos por detalle
            $detallesCalculados = [];
            foreach ($data['detalles'] as $det) {
                $calculo = $this->calculador->calcularDetalle(
                    cantidad: (float) $det['cantidad'],
                    precioUnitario: (float) $det['precio_unitario'],
                    descuento: (float) ($det['descuento'] ?? 0),
                    impuestoIvaId: (int) $det['impuesto_iva_id'],
                );
                $detallesCalculados[] = array_merge($det, $calculo);
            }

            // Totalizar
            $totales = $this->calculador->totalizar($detallesCalculados);

            // Crear factura
            $factura = Factura::create([
                'emisor_id' => $emisor->id,
                'establecimiento_id' => $data['establecimiento_id'],
                'pto_emision_id' => $data['pto_emision_id'],
                'cliente_id' => $data['cliente_id'],
                'secuencial' => $secuencial,
                'fecha_emision' => $data['fecha_emision'],
                'guia_remision' => $data['guia_remision'] ?? null,
                'total_sin_impuestos' => $totales['total_sin_impuestos'],
                'total_descuento' => $totales['total_descuento'],
                'total_iva' => $totales['total_iva'],
                'total_ice' => $totales['total_ice'],
                'total_irbpnr' => $totales['total_irbpnr'],
                'importe_total' => $totales['importe_total'],
                'forma_pago' => $data['forma_pago'] ?? '01',
                'forma_pago_valor' => $totales['importe_total'],
                'observaciones' => $data['observaciones'] ?? null,
                'estado' => 'CREADA',
                'user_id' => auth()->id(),
            ]);

            // Crear detalles con sus impuestos
            foreach ($detallesCalculados as $det) {
                $detalle = FacturaDetalle::create([
                    'factura_id' => $factura->id,
                    'codigo_principal' => $det['codigo_principal'] ?? null,
                    'descripcion' => $det['descripcion'],
                    'cantidad' => $det['cantidad'],
                    'precio_unitario' => $det['precio_unitario'],
                    'descuento' => $det['descuento'] ?? 0,
                    'precio_total_sin_impuesto' => $det['precio_total_sin_impuesto'],
                ]);

                // Impuestos polimórficos
                foreach ($det['impuestos'] as $imp) {
                    Impuesto::create([
                        'detalle_type' => FacturaDetalle::class,
                        'detalle_id' => $detalle->id,
                        'codigo' => $imp['codigo'],
                        'codigo_porcentaje' => $imp['codigo_porcentaje'],
                        'tarifa' => $imp['tarifa'],
                        'base_imponible' => $imp['base_imponible'],
                        'valor' => $imp['valor'],
                    ]);
                }
            }

            // Guía de remisión integrada
            if (!empty($data['con_guia'])) {
                InfoGuiaRemision::create([
                    'factura_id' => $factura->id,
                    'dir_partida' => $data['guia_dir_partida'],
                    'dir_llegada' => $data['guia_dir_llegada'] ?? null,
                    'ruc_transportista' => $data['guia_ruc_transportista'],
                    'razon_social_transportista' => $data['guia_razon_social_transportista'],
                    'placa' => $data['guia_placa'] ?? null,
                    'fecha_ini_transporte' => $data['guia_fecha_ini_transporte'],
                    'fecha_fin_transporte' => $data['guia_fecha_fin_transporte'],
                ]);
            }

            // Reembolsos
            if (!empty($data['es_reembolso']) && !empty($data['reembolsos'])) {
                $factura->update(['es_reembolso' => true, 'cod_doc_reembolso' => '41']);
                foreach ($data['reembolsos'] as $r) {
                    FacturaReembolso::create([
                        'factura_id' => $factura->id,
                        'tipo_identificacion_proveedor' => $r['tipo_proveedor'] === '01' ? '05' : '04',
                        'identificacion_proveedor' => $r['identificacion_proveedor'],
                        'tipo_proveedor' => $r['tipo_proveedor'],
                        'cod_doc_reembolso' => '01',
                        'estab_doc_reembolso' => $r['estab_doc_reembolso'],
                        'pto_emision_doc_reembolso' => $r['pto_emision_doc_reembolso'],
                        'secuencial_doc_reembolso' => $r['secuencial_doc_reembolso'],
                        'fecha_emision_doc_reembolso' => $r['fecha_emision_doc_reembolso'],
                        'numero_autorizacion_doc_reembolso' => $r['numero_autorizacion_doc_reembolso'] ?? null,
                        'base_imponible' => ($r['base_0'] ?? 0) + ($r['base_15'] ?? 0),
                        'impuesto_valor' => $r['iva'] ?? 0,
                    ]);
                }
            }

            return $factura->fresh();
        });
    }
}
