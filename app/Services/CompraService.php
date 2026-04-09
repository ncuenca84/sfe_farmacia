<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Emisor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CompraService
{
    public function __construct(
        private InventarioService $inventarioService,
    ) {}

    /**
     * Crea una compra a partir de datos parseados de un XML del SRI.
     */
    public function crearDesdeXml(
        Emisor $emisor,
        array $datosXml,
        int $clienteId,
        int $establecimientoId,
        array $detallesExtra,
        int $userId,
    ): Compra {
        return DB::transaction(function () use ($emisor, $datosXml, $clienteId, $establecimientoId, $detallesExtra, $userId) {
            $compra = Compra::create([
                'emisor_id' => $emisor->id,
                'cliente_id' => $clienteId,
                'establecimiento_id' => $establecimientoId,
                'user_id' => $userId,
                'tipo_comprobante' => $datosXml['info_tributaria']['cod_doc'],
                'numero_comprobante' => $datosXml['numero_comprobante'],
                'autorizacion' => $datosXml['autorizacion']['numero_autorizacion'] ?? null,
                'clave_acceso' => $datosXml['info_tributaria']['clave_acceso'],
                'fecha_emision' => Carbon::createFromFormat('d/m/Y', $datosXml['info_factura']['fecha_emision']),
                'total_sin_impuestos' => $datosXml['info_factura']['total_sin_impuestos'],
                'total_iva' => $datosXml['total_iva'],
                'importe_total' => $datosXml['info_factura']['importe_total'],
                'ruc_proveedor' => $datosXml['info_tributaria']['ruc'],
                'razon_social_proveedor' => $datosXml['info_tributaria']['razon_social'],
                'estado' => 'REGISTRADA',
            ]);

            $detallesCreados = [];

            foreach ($datosXml['detalles'] as $index => $detalle) {
                $iva = collect($detalle['impuestos'])
                    ->where('codigo', '2')
                    ->sum('valor');

                $subtotal = $detalle['precio_total_sin_impuesto'];
                $productoId = $detallesExtra[$index]['producto_id'] ?? null;
                $agregarInventario = $detallesExtra[$index]['agregar_inventario'] ?? false;

                $detallesCreados[] = CompraDetalle::create([
                    'compra_id' => $compra->id,
                    'codigo_principal' => $detalle['codigo_principal'],
                    'codigo_auxiliar' => $detalle['codigo_auxiliar'],
                    'descripcion' => $detalle['descripcion'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $subtotal,
                    'iva' => $iva,
                    'total' => $subtotal + $iva,
                    'producto_id' => $productoId,
                    'agregar_inventario' => $agregarInventario,
                ]);
            }

            // Procesar inventario si el establecimiento maneja inventario
            $establecimiento = \App\Models\Establecimiento::find($establecimientoId);
            if ($establecimiento && $establecimiento->maneja_inventario) {
                foreach ($detallesCreados as $detalleCreado) {
                    if ($detalleCreado->agregar_inventario && $detalleCreado->producto_id !== null) {
                        $this->inventarioService->registrarEntrada(
                            emisor: $emisor,
                            productoId: $detalleCreado->producto_id,
                            establecimientoId: $establecimientoId,
                            cantidad: (float) $detalleCreado->cantidad,
                            costoUnitario: (float) $detalleCreado->precio_unitario,
                            descripcion: "Compra {$compra->numero_comprobante}",
                            referencia: $compra,
                        );
                    }
                }
            }

            return $compra;
        });
    }

    /**
     * Verifica si ya existe una compra con la clave de acceso dada para el emisor.
     */
    public function existeCompra(Emisor $emisor, string $claveAcceso): bool
    {
        return Compra::where('emisor_id', $emisor->id)
            ->where('clave_acceso', $claveAcceso)
            ->exists();
    }
}
