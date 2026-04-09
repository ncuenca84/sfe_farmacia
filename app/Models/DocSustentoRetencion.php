<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocSustentoRetencion extends Model
{
    protected $table = 'doc_sustento_retenciones';

    protected $fillable = [
        'retencion_ats_id', 'cod_sustento', 'cod_doc_sustento', 'num_doc_sustento',
        'fecha_emision_doc_sustento', 'fecha_registro_contable', 'num_aut_doc_sustento',
        'pago_loc_ext', 'total_sin_impuestos', 'total_iva', 'importe_total', 'forma_pago',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision_doc_sustento' => 'date',
            'fecha_registro_contable' => 'date',
            'total_sin_impuestos' => 'decimal:2',
            'total_iva' => 'decimal:2',
            'importe_total' => 'decimal:2',
        ];
    }

    public function retencionAts(): BelongsTo
    {
        return $this->belongsTo(RetencionAts::class);
    }

    public function desgloses(): HasMany
    {
        return $this->hasMany(DesgloceRetencion::class, 'doc_sustento_retencion_id');
    }

    public function impuestos(): HasMany
    {
        return $this->hasMany(ImpuestoDocSustento::class, 'doc_sustento_retencion_id');
    }
}
