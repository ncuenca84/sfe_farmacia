<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetencionImpuesto extends Model
{
    protected $table = 'retencion_impuestos';

    protected $fillable = [
        'retencion_id', 'codigo_impuesto', 'codigo_retencion',
        'base_imponible', 'porcentaje_retener', 'valor_retenido',
        'cod_doc_sustento', 'num_doc_sustento', 'fecha_emision_doc_sustento',
    ];

    protected function casts(): array
    {
        return [
            'base_imponible' => 'decimal:2',
            'porcentaje_retener' => 'decimal:2',
            'valor_retenido' => 'decimal:2',
            'fecha_emision_doc_sustento' => 'date',
        ];
    }

    public function retencion(): BelongsTo
    {
        return $this->belongsTo(Retencion::class);
    }
}
