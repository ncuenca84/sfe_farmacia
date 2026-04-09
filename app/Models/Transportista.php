<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transportista extends Model
{
    protected $fillable = [
        'emisor_id', 'unidad_negocio_id', 'tipo_identificacion', 'identificacion',
        'razon_social', 'placa', 'email', 'telefono',
    ];

    public function emisor(): BelongsTo
    {
        return $this->belongsTo(Emisor::class);
    }

    public function unidadNegocio(): BelongsTo
    {
        return $this->belongsTo(UnidadNegocio::class);
    }
}
