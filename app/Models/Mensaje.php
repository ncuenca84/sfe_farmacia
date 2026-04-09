<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Mensaje extends Model
{
    protected $table = 'mensajes';

    protected $fillable = [
        'comprobante_type', 'comprobante_id',
        'identificador', 'mensaje', 'informacion_adicional', 'tipo',
    ];

    public function comprobante(): MorphTo
    {
        return $this->morphTo();
    }
}
