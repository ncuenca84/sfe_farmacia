<?php

namespace App\Enums;

enum FormaPago: string
{
    case SIN_SISTEMA_FINANCIERO = '01';
    case COMPENSACION_DEUDAS = '15';
    case TARJETA_DEBITO = '16';
    case DINERO_ELECTRONICO = '17';
    case TARJETA_PREPAGO = '18';
    case TARJETA_CREDITO = '19';
    case OTROS_CON_SISTEMA_FINANCIERO = '20';
    case ENDOSO_TITULOS = '21';

    public function nombre(): string
    {
        return match ($this) {
            self::SIN_SISTEMA_FINANCIERO => 'Sin utilización del sistema financiero',
            self::COMPENSACION_DEUDAS => 'Compensación de deudas',
            self::TARJETA_DEBITO => 'Tarjeta de débito',
            self::DINERO_ELECTRONICO => 'Dinero electrónico',
            self::TARJETA_PREPAGO => 'Tarjeta prepago',
            self::TARJETA_CREDITO => 'Tarjeta de crédito',
            self::OTROS_CON_SISTEMA_FINANCIERO => 'Otros con utilización del sistema financiero',
            self::ENDOSO_TITULOS => 'Endoso de títulos',
        };
    }
}
