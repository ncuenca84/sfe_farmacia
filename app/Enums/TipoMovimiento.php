<?php

namespace App\Enums;

enum TipoMovimiento: string
{
    case ENTRADA = 'ENTRADA';
    case SALIDA = 'SALIDA';
    case AJUSTE = 'AJUSTE';
    case TRANSFERENCIA = 'TRANSFERENCIA';

    public function nombre(): string
    {
        return match ($this) {
            self::ENTRADA => 'Entrada',
            self::SALIDA => 'Salida',
            self::AJUSTE => 'Ajuste',
            self::TRANSFERENCIA => 'Transferencia',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::ENTRADA => 'bg-green-100 text-green-800',
            self::SALIDA => 'bg-red-100 text-red-800',
            self::AJUSTE => 'bg-yellow-100 text-yellow-800',
            self::TRANSFERENCIA => 'bg-blue-100 text-blue-800',
        };
    }
}
