<?php

namespace App\Enums;

enum EstadoComprobante: string
{
    case CREADA = 'CREADA';
    case FIRMADA = 'FIRMADA';
    case AUTORIZADO = 'AUTORIZADO';
    case NO_AUTORIZADO = 'NO AUTORIZADO';
    case DEVUELTA = 'DEVUELTA';
    case ANULADA = 'ANULADA';
    case EN_PROCESO = 'EN PROCESO';
    case PROCESANDOSE = 'PROCESANDOSE';

    public function badge(): string
    {
        return match ($this) {
            self::CREADA => 'bg-secondary',
            self::FIRMADA => 'bg-primary',
            self::AUTORIZADO => 'bg-success',
            self::NO_AUTORIZADO => 'bg-danger',
            self::DEVUELTA => 'bg-warning',
            self::ANULADA => 'bg-dark',
            self::EN_PROCESO => 'bg-info',
            self::PROCESANDOSE => 'bg-warning',
        };
    }
}
