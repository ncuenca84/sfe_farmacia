<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesgloceRetencion extends Model
{
    protected $table = 'desgloce_retenciones';

    protected $fillable = [
        'doc_sustento_retencion_id', 'codigo_impuesto', 'codigo_retencion',
        'base_imponible', 'porcentaje_retener', 'valor_retenido',
    ];

    protected function casts(): array
    {
        return [
            'base_imponible' => 'decimal:2',
            'porcentaje_retener' => 'decimal:2',
            'valor_retenido' => 'decimal:2',
        ];
    }

    public function docSustento(): BelongsTo
    {
        return $this->belongsTo(DocSustentoRetencion::class, 'doc_sustento_retencion_id');
    }
}
