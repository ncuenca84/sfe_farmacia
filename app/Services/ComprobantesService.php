<?php

namespace App\Services;

use App\Models\Emisor;
use App\Models\GuiaDetalle;
use App\Models\Guia;
use App\Models\Impuesto;
use App\Models\LiquidacionCompra;
use App\Models\LiquidacionDetalle;
use App\Models\NotaCredito;
use App\Models\NotaCreditoDetalle;
use App\Models\NotaDebito;
use App\Models\NotaDebitoMotivo;
use App\Models\Proforma;
use App\Models\ProformaDetalle;
use App\Models\PtoEmision;
use App\Models\Retencion;
use App\Models\RetencionImpuesto;
use Illuminate\Support\Facades\DB;

class ComprobantesService
{
    public function __construct(
        private CalculadorImpuestosService $calculador
    ) {}

    public function crearNotaCredito(Emisor $emisor, array $data): NotaCredito
    {
        return DB::transaction(function () use ($emisor, $data) {
            $ptoEmision = PtoEmision::findOrFail($data['pto_emision_id']);
            $secuencial = $ptoEmision->siguienteSecuencial('nota_credito');

            $detallesCalculados = $this->calcularDetalles($data['detalles']);
            $totales = $this->calculador->totalizar($detallesCalculados);

            $nc = NotaCredito::create([
                'emisor_id' => $emisor->id,
                'establecimiento_id' => $data['establecimiento_id'],
                'pto_emision_id' => $data['pto_emision_id'],
                'cliente_id' => $data['cliente_id'],
                'secuencial' => $secuencial,
                'fecha_emision' => $data['fecha_emision'],
                'cod_doc_modificado' => $data['cod_doc_modificado'] ?? '01',
                'num_doc_modificado' => $data['num_doc_modificado'],
                'fecha_emision_doc_sustento' => $data['fecha_emision_doc_sustento'],
                'motivo' => $data['motivo'],
                'observaciones' => $data['observaciones'] ?? null,
                'total_sin_impuestos' => $totales['total_sin_impuestos'],
                'total_descuento' => $totales['total_descuento'],
                'total_iva' => $totales['total_iva'],
                'total_ice' => $totales['total_ice'],
                'importe_total' => $totales['importe_total'],
                'estado' => 'CREADA',
                'user_id' => auth()->id(),
            ]);

            foreach ($detallesCalculados as $det) {
                $detalle = NotaCreditoDetalle::create([
                    'nota_credito_id' => $nc->id,
                    'codigo_principal' => $det['codigo_principal'] ?? null,
                    'descripcion' => $det['descripcion'],
                    'cantidad' => $det['cantidad'],
                    'precio_unitario' => $det['precio_unitario'],
                    'descuento' => $det['descuento'] ?? 0,
                    'precio_total_sin_impuesto' => $det['precio_total_sin_impuesto'],
                ]);

                foreach ($det['impuestos'] as $imp) {
                    Impuesto::create([
                        'detalle_type' => NotaCreditoDetalle::class,
                        'detalle_id' => $detalle->id,
                        'codigo' => $imp['codigo'],
                        'codigo_porcentaje' => $imp['codigo_porcentaje'],
                        'tarifa' => $imp['tarifa'],
                        'base_imponible' => $imp['base_imponible'],
                        'valor' => $imp['valor'],
                    ]);
                }
            }

            return $nc->fresh();
        });
    }

    public function crearNotaDebito(Emisor $emisor, array $data): NotaDebito
    {
        return DB::transaction(function () use ($emisor, $data) {
            $ptoEmision = PtoEmision::findOrFail($data['pto_emision_id']);
            $secuencial = $ptoEmision->siguienteSecuencial('nota_debito');

            $totalSinImpuestos = 0;
            $totalIva = 0;
            $totalIce = 0;

            foreach ($data['motivos'] as $motivo) {
                $valor = (float) $motivo['valor'];
                $totalSinImpuestos += $valor;

                $iva = \App\Models\ImpuestoIva::find($motivo['impuesto_iva_id']);
                if ($iva) {
                    $totalIva += round($valor * ($iva->tarifa / 100), 2);
                }
            }

            $totalSinImpuestos = round($totalSinImpuestos, 2);
            $totalIva = round($totalIva, 2);
            $importeTotal = round($totalSinImpuestos + $totalIva + $totalIce, 2);

            $nd = NotaDebito::create([
                'emisor_id' => $emisor->id,
                'establecimiento_id' => $data['establecimiento_id'],
                'pto_emision_id' => $data['pto_emision_id'],
                'cliente_id' => $data['cliente_id'],
                'secuencial' => $secuencial,
                'fecha_emision' => $data['fecha_emision'],
                'cod_doc_modificado' => $data['cod_doc_modificado'] ?? '01',
                'num_doc_modificado' => $data['num_doc_modificado'],
                'fecha_emision_doc_sustento' => $data['fecha_emision_doc_sustento'],
                'observaciones' => $data['observaciones'] ?? null,
                'total_sin_impuestos' => $totalSinImpuestos,
                'total_descuento' => 0,
                'total_iva' => $totalIva,
                'total_ice' => $totalIce,
                'importe_total' => $importeTotal,
                'estado' => 'CREADA',
                'user_id' => auth()->id(),
            ]);

            foreach ($data['motivos'] as $motivo) {
                NotaDebitoMotivo::create([
                    'nota_debito_id' => $nd->id,
                    'razon' => $motivo['razon'],
                    'valor' => $motivo['valor'],
                    'impuesto_iva_id' => $motivo['impuesto_iva_id'],
                ]);
            }

            return $nd->fresh();
        });
    }

    public function crearRetencion(Emisor $emisor, array $data): Retencion
    {
        return DB::transaction(function () use ($emisor, $data) {
            $ptoEmision = PtoEmision::findOrFail($data['pto_emision_id']);
            $secuencial = $ptoEmision->siguienteSecuencial('retencion');

            $ret = Retencion::create([
                'emisor_id' => $emisor->id,
                'establecimiento_id' => $data['establecimiento_id'],
                'pto_emision_id' => $data['pto_emision_id'],
                'cliente_id' => $data['cliente_id'],
                'secuencial' => $secuencial,
                'fecha_emision' => $data['fecha_emision'],
                'cod_doc_sustento' => $data['tipo_doc_sustento'],
                'num_doc_sustento' => $data['num_doc_sustento'],
                'fecha_emision_doc_sustento' => $data['fecha_emision_doc_sustento'],
                'estado' => 'CREADA',
                'user_id' => auth()->id(),
            ]);

            foreach ($data['impuestos'] as $imp) {
                RetencionImpuesto::create([
                    'retencion_id' => $ret->id,
                    'codigo_impuesto' => $imp['codigo'],
                    'codigo_retencion' => $imp['codigo_retencion'],
                    'base_imponible' => $imp['base_imponible'],
                    'porcentaje_retener' => $imp['porcentaje_retener'],
                    'valor_retenido' => $imp['valor_retenido'],
                    'cod_doc_sustento' => $data['tipo_doc_sustento'],
                    'num_doc_sustento' => $data['num_doc_sustento'],
                    'fecha_emision_doc_sustento' => $data['fecha_emision_doc_sustento'],
                ]);
            }

            return $ret->fresh();
        });
    }

    public function crearLiquidacion(Emisor $emisor, array $data): LiquidacionCompra
    {
        return DB::transaction(function () use ($emisor, $data) {
            $ptoEmision = PtoEmision::findOrFail($data['pto_emision_id']);
            $secuencial = $ptoEmision->siguienteSecuencial('liquidacion');

            $detallesCalculados = $this->calcularDetalles($data['detalles']);
            $totales = $this->calculador->totalizar($detallesCalculados);

            $liq = LiquidacionCompra::create([
                'emisor_id' => $emisor->id,
                'establecimiento_id' => $data['establecimiento_id'],
                'pto_emision_id' => $data['pto_emision_id'],
                'cliente_id' => $data['cliente_id'],
                'secuencial' => $secuencial,
                'fecha_emision' => $data['fecha_emision'],
                'total_sin_impuestos' => $totales['total_sin_impuestos'],
                'total_descuento' => $totales['total_descuento'],
                'total_iva' => $totales['total_iva'],
                'total_ice' => $totales['total_ice'],
                'importe_total' => $totales['importe_total'],
                'forma_pago' => $data['forma_pago'] ?? '01',
                'forma_pago_valor' => $totales['importe_total'],
                'observaciones' => $data['observaciones'] ?? null,
                'estado' => 'CREADA',
                'user_id' => auth()->id(),
            ]);

            foreach ($detallesCalculados as $det) {
                $detalle = LiquidacionDetalle::create([
                    'liquidacion_compra_id' => $liq->id,
                    'codigo_principal' => $det['codigo_principal'] ?? null,
                    'descripcion' => $det['descripcion'],
                    'cantidad' => $det['cantidad'],
                    'precio_unitario' => $det['precio_unitario'],
                    'descuento' => $det['descuento'] ?? 0,
                    'precio_total_sin_impuesto' => $det['precio_total_sin_impuesto'],
                ]);

                foreach ($det['impuestos'] as $imp) {
                    Impuesto::create([
                        'detalle_type' => LiquidacionDetalle::class,
                        'detalle_id' => $detalle->id,
                        'codigo' => $imp['codigo'],
                        'codigo_porcentaje' => $imp['codigo_porcentaje'],
                        'tarifa' => $imp['tarifa'],
                        'base_imponible' => $imp['base_imponible'],
                        'valor' => $imp['valor'],
                    ]);
                }
            }

            return $liq->fresh();
        });
    }

    public function crearGuia(Emisor $emisor, array $data): Guia
    {
        return DB::transaction(function () use ($emisor, $data) {
            $ptoEmision = PtoEmision::findOrFail($data['pto_emision_id']);
            $secuencial = $ptoEmision->siguienteSecuencial('guia');

            $guia = Guia::create([
                'emisor_id' => $emisor->id,
                'establecimiento_id' => $data['establecimiento_id'],
                'pto_emision_id' => $data['pto_emision_id'],
                'secuencial' => $secuencial,
                'fecha_emision' => $data['fecha_emision'],
                'dir_partida' => $data['dir_partida'],
                'dir_llegada' => $data['dir_llegada'] ?? null,
                'ruc_transportista' => $data['ruc_transportista'],
                'razon_social_transportista' => $data['razon_social_transportista'],
                'placa' => $data['placa'],
                'fecha_ini_transporte' => $data['fecha_inicio_transporte'],
                'fecha_fin_transporte' => $data['fecha_fin_transporte'],
                'observaciones' => $data['observaciones'] ?? null,
                'estado' => 'CREADA',
                'user_id' => auth()->id(),
            ]);

            foreach ($data['destinatarios'] as $dest) {
                $productos = $dest['productos'] ?? [];
                if (empty($productos)) {
                    // Create one detail record without product info
                    GuiaDetalle::create([
                        'guia_id' => $guia->id,
                        'identificacion_destinatario' => $dest['identificacion'],
                        'razon_social_destinatario' => $dest['razon_social'],
                        'dir_destinatario' => $dest['direccion'],
                        'motivo_traslado' => $dest['motivo_traslado'],
                        'doc_aduanero_unico' => $dest['doc_aduanero_unico'] ?? null,
                        'cod_establecimiento_destino' => $dest['cod_establecimiento_destino'] ?? null,
                        'ruta' => $dest['ruta'] ?? null,
                    ]);
                } else {
                    foreach ($productos as $prod) {
                        GuiaDetalle::create([
                            'guia_id' => $guia->id,
                            'identificacion_destinatario' => $dest['identificacion'],
                            'razon_social_destinatario' => $dest['razon_social'],
                            'dir_destinatario' => $dest['direccion'],
                            'motivo_traslado' => $dest['motivo_traslado'],
                            'doc_aduanero_unico' => $dest['doc_aduanero_unico'] ?? null,
                            'cod_establecimiento_destino' => $dest['cod_establecimiento_destino'] ?? null,
                            'ruta' => $dest['ruta'] ?? null,
                            'codigo_principal' => $prod['codigo_principal'] ?? null,
                            'descripcion' => $prod['descripcion'] ?? null,
                            'cantidad' => $prod['cantidad'] ?? null,
                        ]);
                    }
                }
            }

            return $guia->fresh();
        });
    }

    public function crearProforma(Emisor $emisor, array $data): Proforma
    {
        return DB::transaction(function () use ($emisor, $data) {
            $ptoEmision = PtoEmision::findOrFail($data['pto_emision_id'] ?? $emisor->establecimientos()->first()?->ptoEmisiones()->first()?->id);
            $secuencial = $ptoEmision->siguienteSecuencial('proforma');

            $detallesCalculados = $this->calcularDetalles($data['detalles']);
            $totales = $this->calculador->totalizar($detallesCalculados);

            $proforma = Proforma::create([
                'emisor_id' => $emisor->id,
                'establecimiento_id' => $data['establecimiento_id'],
                'pto_emision_id' => $ptoEmision->id,
                'cliente_id' => $data['cliente_id'],
                'secuencial' => $secuencial,
                'fecha_emision' => $data['fecha_emision'],
                'fecha_vencimiento' => $data['fecha_vencimiento'] ?? null,
                'observaciones' => $data['observaciones'] ?? null,
                'total_sin_impuestos' => $totales['total_sin_impuestos'],
                'total_descuento' => $totales['total_descuento'],
                'total_iva' => $totales['total_iva'],
                'total_ice' => $totales['total_ice'],
                'importe_total' => $totales['importe_total'],
                'estado' => 'VIGENTE',
                'user_id' => auth()->id(),
            ]);

            foreach ($detallesCalculados as $det) {
                $detalle = ProformaDetalle::create([
                    'proforma_id' => $proforma->id,
                    'codigo_principal' => $det['codigo_principal'] ?? null,
                    'descripcion' => $det['descripcion'],
                    'cantidad' => $det['cantidad'],
                    'precio_unitario' => $det['precio_unitario'],
                    'descuento' => $det['descuento'] ?? 0,
                    'precio_total_sin_impuesto' => $det['precio_total_sin_impuesto'],
                ]);

                foreach ($det['impuestos'] as $imp) {
                    Impuesto::create([
                        'detalle_type' => ProformaDetalle::class,
                        'detalle_id' => $detalle->id,
                        'codigo' => $imp['codigo'],
                        'codigo_porcentaje' => $imp['codigo_porcentaje'],
                        'tarifa' => $imp['tarifa'],
                        'base_imponible' => $imp['base_imponible'],
                        'valor' => $imp['valor'],
                    ]);
                }
            }

            return $proforma->fresh();
        });
    }

    public function actualizarNotaCredito(NotaCredito $nc, array $data): NotaCredito
    {
        return DB::transaction(function () use ($nc, $data) {
            $detallesCalculados = $this->calcularDetalles($data['detalles']);
            $totales = $this->calculador->totalizar($detallesCalculados);

            $nc->update([
                'establecimiento_id' => $data['establecimiento_id'],
                'pto_emision_id' => $data['pto_emision_id'],
                'cliente_id' => $data['cliente_id'],
                'fecha_emision' => $data['fecha_emision'],
                'cod_doc_modificado' => $data['cod_doc_modificado'] ?? '01',
                'num_doc_modificado' => $data['num_doc_modificado'],
                'fecha_emision_doc_sustento' => $data['fecha_emision_doc_sustento'],
                'motivo' => $data['motivo'],
                'observaciones' => $data['observaciones'] ?? null,
                'total_sin_impuestos' => $totales['total_sin_impuestos'],
                'total_descuento' => $totales['total_descuento'],
                'total_iva' => $totales['total_iva'],
                'total_ice' => $totales['total_ice'],
                'importe_total' => $totales['importe_total'],
            ]);

            foreach ($nc->detalles as $detalle) {
                $detalle->impuestos()->delete();
                $detalle->delete();
            }

            foreach ($detallesCalculados as $det) {
                $detalle = NotaCreditoDetalle::create([
                    'nota_credito_id' => $nc->id,
                    'codigo_principal' => $det['codigo_principal'] ?? null,
                    'descripcion' => $det['descripcion'],
                    'cantidad' => $det['cantidad'],
                    'precio_unitario' => $det['precio_unitario'],
                    'descuento' => $det['descuento'] ?? 0,
                    'precio_total_sin_impuesto' => $det['precio_total_sin_impuesto'],
                ]);

                foreach ($det['impuestos'] as $imp) {
                    Impuesto::create([
                        'detalle_type' => NotaCreditoDetalle::class,
                        'detalle_id' => $detalle->id,
                        'codigo' => $imp['codigo'],
                        'codigo_porcentaje' => $imp['codigo_porcentaje'],
                        'tarifa' => $imp['tarifa'],
                        'base_imponible' => $imp['base_imponible'],
                        'valor' => $imp['valor'],
                    ]);
                }
            }

            return $nc->fresh();
        });
    }

    public function actualizarNotaDebito(NotaDebito $nd, array $data): NotaDebito
    {
        return DB::transaction(function () use ($nd, $data) {
            $totalSinImpuestos = 0;
            $totalIva = 0;
            $totalIce = 0;

            foreach ($data['motivos'] as $motivo) {
                $valor = (float) $motivo['valor'];
                $totalSinImpuestos += $valor;

                $iva = \App\Models\ImpuestoIva::find($motivo['impuesto_iva_id']);
                if ($iva) {
                    $totalIva += round($valor * ($iva->tarifa / 100), 2);
                }
            }

            $totalSinImpuestos = round($totalSinImpuestos, 2);
            $totalIva = round($totalIva, 2);
            $importeTotal = round($totalSinImpuestos + $totalIva + $totalIce, 2);

            $nd->update([
                'establecimiento_id' => $data['establecimiento_id'],
                'pto_emision_id' => $data['pto_emision_id'],
                'cliente_id' => $data['cliente_id'],
                'fecha_emision' => $data['fecha_emision'],
                'cod_doc_modificado' => $data['cod_doc_modificado'] ?? '01',
                'num_doc_modificado' => $data['num_doc_modificado'],
                'fecha_emision_doc_sustento' => $data['fecha_emision_doc_sustento'],
                'observaciones' => $data['observaciones'] ?? null,
                'total_sin_impuestos' => $totalSinImpuestos,
                'total_descuento' => 0,
                'total_iva' => $totalIva,
                'total_ice' => $totalIce,
                'importe_total' => $importeTotal,
            ]);

            $nd->motivos()->delete();

            foreach ($data['motivos'] as $motivo) {
                NotaDebitoMotivo::create([
                    'nota_debito_id' => $nd->id,
                    'razon' => $motivo['razon'],
                    'valor' => $motivo['valor'],
                    'impuesto_iva_id' => $motivo['impuesto_iva_id'],
                ]);
            }

            return $nd->fresh();
        });
    }

    public function actualizarLiquidacion(LiquidacionCompra $liq, array $data): LiquidacionCompra
    {
        return DB::transaction(function () use ($liq, $data) {
            $detallesCalculados = $this->calcularDetalles($data['detalles']);
            $totales = $this->calculador->totalizar($detallesCalculados);

            $liq->update([
                'establecimiento_id' => $data['establecimiento_id'],
                'pto_emision_id' => $data['pto_emision_id'],
                'cliente_id' => $data['cliente_id'],
                'fecha_emision' => $data['fecha_emision'],
                'total_sin_impuestos' => $totales['total_sin_impuestos'],
                'total_descuento' => $totales['total_descuento'],
                'total_iva' => $totales['total_iva'],
                'total_ice' => $totales['total_ice'],
                'importe_total' => $totales['importe_total'],
                'forma_pago' => $data['forma_pago'] ?? '01',
                'forma_pago_valor' => $totales['importe_total'],
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            foreach ($liq->detalles as $detalle) {
                $detalle->impuestos()->delete();
                $detalle->delete();
            }

            foreach ($detallesCalculados as $det) {
                $detalle = LiquidacionDetalle::create([
                    'liquidacion_compra_id' => $liq->id,
                    'codigo_principal' => $det['codigo_principal'] ?? null,
                    'descripcion' => $det['descripcion'],
                    'cantidad' => $det['cantidad'],
                    'precio_unitario' => $det['precio_unitario'],
                    'descuento' => $det['descuento'] ?? 0,
                    'precio_total_sin_impuesto' => $det['precio_total_sin_impuesto'],
                ]);

                foreach ($det['impuestos'] as $imp) {
                    Impuesto::create([
                        'detalle_type' => LiquidacionDetalle::class,
                        'detalle_id' => $detalle->id,
                        'codigo' => $imp['codigo'],
                        'codigo_porcentaje' => $imp['codigo_porcentaje'],
                        'tarifa' => $imp['tarifa'],
                        'base_imponible' => $imp['base_imponible'],
                        'valor' => $imp['valor'],
                    ]);
                }
            }

            return $liq->fresh();
        });
    }

    public function actualizarGuia(Guia $guia, array $data): Guia
    {
        return DB::transaction(function () use ($guia, $data) {
            $guia->update([
                'establecimiento_id' => $data['establecimiento_id'],
                'pto_emision_id' => $data['pto_emision_id'],
                'fecha_emision' => $data['fecha_emision'],
                'dir_partida' => $data['dir_partida'],
                'dir_llegada' => $data['dir_llegada'] ?? null,
                'ruc_transportista' => $data['ruc_transportista'],
                'razon_social_transportista' => $data['razon_social_transportista'],
                'placa' => $data['placa'],
                'fecha_ini_transporte' => $data['fecha_inicio_transporte'],
                'fecha_fin_transporte' => $data['fecha_fin_transporte'],
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            $guia->detalles()->delete();

            foreach ($data['destinatarios'] as $dest) {
                $productos = $dest['productos'] ?? [];
                if (empty($productos)) {
                    GuiaDetalle::create([
                        'guia_id' => $guia->id,
                        'identificacion_destinatario' => $dest['identificacion'],
                        'razon_social_destinatario' => $dest['razon_social'],
                        'dir_destinatario' => $dest['direccion'],
                        'motivo_traslado' => $dest['motivo_traslado'],
                        'doc_aduanero_unico' => $dest['doc_aduanero_unico'] ?? null,
                        'cod_establecimiento_destino' => $dest['cod_establecimiento_destino'] ?? null,
                        'ruta' => $dest['ruta'] ?? null,
                    ]);
                } else {
                    foreach ($productos as $prod) {
                        GuiaDetalle::create([
                            'guia_id' => $guia->id,
                            'identificacion_destinatario' => $dest['identificacion'],
                            'razon_social_destinatario' => $dest['razon_social'],
                            'dir_destinatario' => $dest['direccion'],
                            'motivo_traslado' => $dest['motivo_traslado'],
                            'doc_aduanero_unico' => $dest['doc_aduanero_unico'] ?? null,
                            'cod_establecimiento_destino' => $dest['cod_establecimiento_destino'] ?? null,
                            'ruta' => $dest['ruta'] ?? null,
                            'codigo_principal' => $prod['codigo_principal'] ?? null,
                            'descripcion' => $prod['descripcion'] ?? null,
                            'cantidad' => $prod['cantidad'] ?? null,
                        ]);
                    }
                }
            }

            return $guia->fresh();
        });
    }

    public function actualizarProforma(Proforma $proforma, array $data): Proforma
    {
        return DB::transaction(function () use ($proforma, $data) {
            $detallesCalculados = $this->calcularDetalles($data['detalles']);
            $totales = $this->calculador->totalizar($detallesCalculados);

            $proforma->update([
                'establecimiento_id' => $data['establecimiento_id'],
                'cliente_id' => $data['cliente_id'],
                'fecha_emision' => $data['fecha_emision'],
                'fecha_vencimiento' => $data['fecha_vencimiento'] ?? null,
                'observaciones' => $data['observaciones'] ?? null,
                'total_sin_impuestos' => $totales['total_sin_impuestos'],
                'total_descuento' => $totales['total_descuento'],
                'total_iva' => $totales['total_iva'],
                'total_ice' => $totales['total_ice'],
                'importe_total' => $totales['importe_total'],
            ]);

            foreach ($proforma->detalles as $detalle) {
                $detalle->impuestos()->delete();
                $detalle->delete();
            }

            foreach ($detallesCalculados as $det) {
                $detalle = ProformaDetalle::create([
                    'proforma_id' => $proforma->id,
                    'codigo_principal' => $det['codigo_principal'] ?? null,
                    'descripcion' => $det['descripcion'],
                    'cantidad' => $det['cantidad'],
                    'precio_unitario' => $det['precio_unitario'],
                    'descuento' => $det['descuento'] ?? 0,
                    'precio_total_sin_impuesto' => $det['precio_total_sin_impuesto'],
                ]);

                foreach ($det['impuestos'] as $imp) {
                    Impuesto::create([
                        'detalle_type' => ProformaDetalle::class,
                        'detalle_id' => $detalle->id,
                        'codigo' => $imp['codigo'],
                        'codigo_porcentaje' => $imp['codigo_porcentaje'],
                        'tarifa' => $imp['tarifa'],
                        'base_imponible' => $imp['base_imponible'],
                        'valor' => $imp['valor'],
                    ]);
                }
            }

            return $proforma->fresh();
        });
    }

    private function calcularDetalles(array $detalles): array
    {
        $detallesCalculados = [];
        foreach ($detalles as $det) {
            $calculo = $this->calculador->calcularDetalle(
                cantidad: (float) $det['cantidad'],
                precioUnitario: (float) $det['precio_unitario'],
                descuento: (float) ($det['descuento'] ?? 0),
                impuestoIvaId: (int) $det['impuesto_iva_id'],
            );
            $detallesCalculados[] = array_merge($det, $calculo);
        }
        return $detallesCalculados;
    }
}
