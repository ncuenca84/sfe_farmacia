<?php

namespace App\Enums;

enum TipoIdentificacion: string
{
    case RUC = '04';
    case CEDULA = '05';
    case PASAPORTE = '06';
    case CONSUMIDOR_FINAL = '07';
    case IDENTIFICACION_EXTERIOR = '08';
    case PLACA = '09';

    public function nombre(): string
    {
        return match ($this) {
            self::RUC => 'RUC',
            self::CEDULA => 'Cédula',
            self::PASAPORTE => 'Pasaporte',
            self::CONSUMIDOR_FINAL => 'Consumidor Final',
            self::IDENTIFICACION_EXTERIOR => 'Identificación del exterior',
            self::PLACA => 'Placa',
        };
    }
}
