<?php

namespace App\Models\Traits;

use App\Models\CampoAdicional;
use App\Models\Mensaje;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait TieneComprobanteSri
{
    public function camposAdicionales(): MorphMany
    {
        return $this->morphMany(CampoAdicional::class, 'comprobante');
    }

    public function mensajes(): MorphMany
    {
        return $this->morphMany(Mensaje::class, 'comprobante');
    }

    public function estaAutorizado(): bool
    {
        return $this->estado === 'AUTORIZADO';
    }

    public function getNumeroCompletoAttribute(): string
    {
        $est = $this->establecimiento->codigo ?? '000';
        $pto = $this->ptoEmision->codigo ?? '000';
        $sec = str_pad($this->secuencial, 9, '0', STR_PAD_LEFT);
        return "{$est}-{$pto}-{$sec}";
    }
}
