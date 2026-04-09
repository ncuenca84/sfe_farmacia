<?php

namespace App\Services;

use SimpleXMLElement;

class XmlParserSriService
{
    /**
     * Parsea un XML de factura autorizada del SRI.
     *
     * @param string $xmlContent Raw XML content
     * @return array Parsed data
     * @throws \InvalidArgumentException if XML is invalid or not a factura
     */
    public function parsearFactura(string $xmlContent): array
    {
        $xmlContent = trim($xmlContent);

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlContent);

        if ($xml === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            throw new \InvalidArgumentException('XML inválido: no se pudo parsear el contenido.');
        }

        $rootName = $xml->getName();

        $autorizacion = [];
        $factura = null;

        if ($rootName === 'autorizaciones') {
            // Wrapper <autorizaciones><autorizacion>...</autorizacion></autorizaciones>
            if (!isset($xml->autorizacion)) {
                throw new \InvalidArgumentException('No se encontró el elemento <autorizacion> dentro de <autorizaciones>.');
            }
            $xml = $xml->autorizacion;
            $rootName = 'autorizacion';
        }

        if ($rootName === 'autorizacion') {
            $estado = (string) $xml->estado;

            if ($estado !== 'AUTORIZADO') {
                throw new \InvalidArgumentException("El comprobante no está autorizado. Estado: {$estado}");
            }

            $autorizacion = [
                'estado' => $estado,
                'numero_autorizacion' => (string) $xml->numeroAutorizacion,
                'fecha_autorizacion' => (string) $xml->fechaAutorizacion,
            ];

            $comprobanteCdata = (string) $xml->comprobante;
            $comprobanteCdata = trim($comprobanteCdata);

            if (empty($comprobanteCdata)) {
                throw new \InvalidArgumentException('El contenido del comprobante está vacío.');
            }

            $factura = simplexml_load_string($comprobanteCdata);

            if ($factura === false) {
                libxml_clear_errors();
                throw new \InvalidArgumentException('XML inválido: no se pudo parsear el comprobante dentro de CDATA.');
            }
        } elseif ($rootName === 'factura') {
            $factura = $xml;
        } else {
            throw new \InvalidArgumentException("Elemento raíz inesperado: {$rootName}. Se esperaba 'autorizacion' o 'factura'.");
        }

        if ($factura->getName() !== 'factura') {
            throw new \InvalidArgumentException('El comprobante no es una factura.');
        }

        $codDoc = (string) $factura->infoTributaria->codDoc;

        if ($codDoc !== '01') {
            throw new \InvalidArgumentException("El tipo de documento no es factura. codDoc: {$codDoc}");
        }

        return $this->extraerDatosFactura($factura, $autorizacion);
    }

    /**
     * Extrae todos los datos de la factura parseada.
     */
    private function extraerDatosFactura(SimpleXMLElement $factura, array $autorizacion): array
    {
        $infoTributaria = $this->extraerInfoTributaria($factura->infoTributaria);
        $infoFactura = $this->extraerInfoFactura($factura->infoFactura);
        $impuestosTotales = $this->extraerImpuestosTotales($factura->infoFactura->totalConImpuestos);
        $detalles = $this->extraerDetalles($factura->detalles);
        $infoAdicional = $this->extraerInfoAdicional($factura);

        $numeroComprobante = sprintf(
            '%s-%s-%s',
            $infoTributaria['estab'],
            $infoTributaria['pto_emi'],
            $infoTributaria['secuencial']
        );

        $totalIva = 0.0;
        foreach ($impuestosTotales as $impuesto) {
            if ($impuesto['codigo'] === '2') {
                $totalIva += $impuesto['valor'];
            }
        }

        return [
            'autorizacion' => $autorizacion,
            'info_tributaria' => $infoTributaria,
            'info_factura' => $infoFactura,
            'impuestos_totales' => $impuestosTotales,
            'detalles' => $detalles,
            'info_adicional' => $infoAdicional,
            'numero_comprobante' => $numeroComprobante,
            'total_iva' => $totalIva,
        ];
    }

    /**
     * Extrae la información tributaria del emisor.
     */
    private function extraerInfoTributaria(SimpleXMLElement $info): array
    {
        return [
            'ambiente' => (string) $info->ambiente,
            'ruc' => (string) $info->ruc,
            'razon_social' => (string) $info->razonSocial,
            'nombre_comercial' => (string) ($info->nombreComercial ?? ''),
            'clave_acceso' => (string) $info->claveAcceso,
            'cod_doc' => (string) $info->codDoc,
            'estab' => (string) $info->estab,
            'pto_emi' => (string) $info->ptoEmi,
            'secuencial' => (string) $info->secuencial,
            'dir_matriz' => (string) $info->dirMatriz,
        ];
    }

    /**
     * Extrae la información general de la factura.
     */
    private function extraerInfoFactura(SimpleXMLElement $info): array
    {
        return [
            'fecha_emision' => (string) $info->fechaEmision,
            'tipo_identificacion_comprador' => (string) $info->tipoIdentificacionComprador,
            'razon_social_comprador' => (string) $info->razonSocialComprador,
            'identificacion_comprador' => (string) $info->identificacionComprador,
            'total_sin_impuestos' => (float) $info->totalSinImpuestos,
            'total_descuento' => (float) $info->totalDescuento,
            'importe_total' => (float) $info->importeTotal,
        ];
    }

    /**
     * Extrae los impuestos totales del comprobante.
     */
    private function extraerImpuestosTotales(SimpleXMLElement $totalConImpuestos): array
    {
        $impuestos = [];

        if (isset($totalConImpuestos->totalImpuesto)) {
            foreach ($totalConImpuestos->totalImpuesto as $impuesto) {
                $impuestos[] = [
                    'codigo' => (string) $impuesto->codigo,
                    'codigo_porcentaje' => (string) $impuesto->codigoPorcentaje,
                    'base_imponible' => (float) $impuesto->baseImponible,
                    'valor' => (float) $impuesto->valor,
                ];
            }
        }

        return $impuestos;
    }

    /**
     * Extrae los detalles (líneas) de la factura.
     */
    private function extraerDetalles(SimpleXMLElement $detalles): array
    {
        $items = [];

        if (isset($detalles->detalle)) {
            foreach ($detalles->detalle as $detalle) {
                $item = [
                    'codigo_principal' => (string) $detalle->codigoPrincipal,
                    'codigo_auxiliar' => (string) ($detalle->codigoAuxiliar ?? ''),
                    'descripcion' => (string) $detalle->descripcion,
                    'cantidad' => (float) $detalle->cantidad,
                    'precio_unitario' => (float) $detalle->precioUnitario,
                    'descuento' => (float) $detalle->descuento,
                    'precio_total_sin_impuesto' => (float) $detalle->precioTotalSinImpuesto,
                    'impuestos' => [],
                ];

                if (isset($detalle->impuestos->impuesto)) {
                    foreach ($detalle->impuestos->impuesto as $impuesto) {
                        $item['impuestos'][] = [
                            'codigo' => (string) $impuesto->codigo,
                            'codigo_porcentaje' => (string) $impuesto->codigoPorcentaje,
                            'tarifa' => (float) $impuesto->tarifa,
                            'base_imponible' => (float) $impuesto->baseImponible,
                            'valor' => (float) $impuesto->valor,
                        ];
                    }
                }

                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * Extrae los campos adicionales de la factura.
     */
    private function extraerInfoAdicional(SimpleXMLElement $factura): array
    {
        $info = [];

        if (isset($factura->infoAdicional->campoAdicional)) {
            foreach ($factura->infoAdicional->campoAdicional as $campo) {
                $nombre = (string) $campo['nombre'];
                $valor = (string) $campo;
                $info[$nombre] = $valor;
            }
        }

        return $info;
    }
}
