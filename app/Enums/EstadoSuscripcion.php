<?php

namespace App\Enums;

enum EstadoSuscripcion: string
{
    case ACTIVA = 'ACTIVA';
    case VENCIDA = 'VENCIDA';
    case SUSPENDIDA = 'SUSPENDIDA';
}
