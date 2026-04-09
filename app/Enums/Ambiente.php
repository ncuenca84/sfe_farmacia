<?php

namespace App\Enums;

enum Ambiente: string
{
    case PRUEBAS = '1';
    case PRODUCCION = '2';

    public function nombre(): string
    {
        return match ($this) {
            self::PRUEBAS => 'Pruebas',
            self::PRODUCCION => 'Producción',
        };
    }
}
