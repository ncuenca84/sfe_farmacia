<?php

namespace App\Models\Traits;

use App\Models\Impuesto;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait TieneImpuestosDetalle
{
    public function impuestos(): MorphMany
    {
        return $this->morphMany(Impuesto::class, 'detalle');
    }
}
