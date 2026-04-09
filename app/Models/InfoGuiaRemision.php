<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InfoGuiaRemision extends Model
{
    protected $table = 'info_guia_remisiones';

    protected $fillable = [
        'factura_id', 'dir_partida', 'ruc_transportista',
        'tipo_identificacion_transportista', 'razon_social_transportista',
        'placa', 'dir_llegada', 'fecha_ini_transporte', 'fecha_fin_transporte',
    ];

    protected function casts(): array
    {
        return [
            'fecha_ini_transporte' => 'date',
            'fecha_fin_transporte' => 'date',
        ];
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class);
    }
}
