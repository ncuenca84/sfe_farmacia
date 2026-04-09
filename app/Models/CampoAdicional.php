<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CampoAdicional extends Model
{
    protected $table = 'campos_adicionales';

    protected $fillable = ['comprobante_type', 'comprobante_id', 'nombre', 'valor'];

    public function comprobante(): MorphTo
    {
        return $this->morphTo();
    }
}
