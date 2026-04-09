<?php

namespace App\Enums;

enum TipoComprobante: string
{
    case FACTURA = '01';
    case LIQUIDACION_COMPRA = '03';
    case NOTA_CREDITO = '04';
    case NOTA_DEBITO = '05';
    case GUIA_REMISION = '06';
    case RETENCION = '07';
    case PROFORMA = '00';

    public function nombre(): string
    {
        return match ($this) {
            self::FACTURA => 'Factura',
            self::LIQUIDACION_COMPRA => 'Liquidación de Compra',
            self::NOTA_CREDITO => 'Nota de Crédito',
            self::NOTA_DEBITO => 'Nota de Débito',
            self::GUIA_REMISION => 'Guía de Remisión',
            self::RETENCION => 'Comprobante de Retención',
            self::PROFORMA => 'Proforma',
        };
    }
}
