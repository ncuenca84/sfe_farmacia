<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImpuestoDocSustento extends Model
{
    protected $table = 'impuesto_doc_sustentos';

    protected $fillable = [
        'doc_sustento_retencion_id', 'codigo_impuesto', 'codigo_porcentaje',
        'base_imponible', 'tarifa', 'valor_impuesto',
    ];

    protected function casts(): array
    {
        return [
            'base_imponible' => 'decimal:2',
            'tarifa' => 'decimal:2',
            'valor_impuesto' => 'decimal:2',
        ];
    }

    public function docSustento(): BelongsTo
    {
        return $this->belongsTo(DocSustentoRetencion::class, 'doc_sustento_retencion_id');
    }
}
