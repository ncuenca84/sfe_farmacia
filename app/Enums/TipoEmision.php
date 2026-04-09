<?php

namespace App\Enums;

enum TipoEmision: string
{
    case NORMAL = '1';

    public function nombre(): string
    {
        return match ($this) {
            self::NORMAL => 'Emisión Normal',
        };
    }
}
