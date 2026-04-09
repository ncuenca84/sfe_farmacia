<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiquidacionReembolso extends Model
{
    protected $fillable = [
        'liquidacion_compra_id', 'tipo_identificacion_proveedor', 'identificacion_proveedor',
        'cod_doc_reembolso', 'estab_doc_reembolso', 'pto_emision_doc_reembolso',
        'secuencial_doc_reembolso', 'fecha_emision_doc_reembolso',
        'numero_autorizacion_doc_reembolso', 'base_imponible', 'impuesto_valor',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision_doc_reembolso' => 'date',
            'base_imponible' => 'decimal:2',
            'impuesto_valor' => 'decimal:2',
        ];
    }

    public function liquidacionCompra(): BelongsTo
    {
        return $this->belongsTo(LiquidacionCompra::class);
    }
}
