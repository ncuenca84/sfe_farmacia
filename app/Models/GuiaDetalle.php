<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuiaDetalle extends Model
{
    protected $table = 'guia_detalles';

    protected $fillable = [
        'guia_id', 'identificacion_destinatario', 'razon_social_destinatario',
        'dir_destinatario', 'motivo_traslado', 'doc_aduanero_unico',
        'cod_establecimiento_destino', 'ruta',
        'cod_doc_sustento', 'num_doc_sustento', 'num_aut_doc_sustento',
        'fecha_emision_doc_sustento',
        'codigo_principal', 'descripcion', 'cantidad',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision_doc_sustento' => 'date',
            'cantidad' => 'decimal:6',
        ];
    }

    public function guia(): BelongsTo
    {
        return $this->belongsTo(Guia::class);
    }
}
