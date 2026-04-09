<?php

namespace App\Models;

use App\Models\Traits\TieneComprobanteSri;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class NotaCredito extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use TieneComprobanteSri;

    protected $fillable = [
        'emisor_id', 'establecimiento_id', 'pto_emision_id', 'cliente_id',
        'secuencial', 'fecha_emision',
        'cod_doc_modificado', 'num_doc_modificado', 'fecha_emision_doc_sustento', 'motivo',
        'total_sin_impuestos', 'total_descuento', 'total_iva', 'total_ice',
        'importe_total', 'moneda',
        'clave_acceso', 'numero_autorizacion', 'fecha_autorizacion',
        'estado', 'ambiente', 'motivo_rechazo', 'xml_path', 'user_id', 'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'fecha_emision_doc_sustento' => 'date',
            'fecha_autorizacion' => 'datetime',
            'total_sin_impuestos' => 'decimal:2',
            'importe_total' => 'decimal:2',
        ];
    }

    public function emisor(): BelongsTo { return $this->belongsTo(Emisor::class); }
    public function establecimiento(): BelongsTo { return $this->belongsTo(Establecimiento::class); }
    public function ptoEmision(): BelongsTo { return $this->belongsTo(PtoEmision::class); }
    public function cliente(): BelongsTo { return $this->belongsTo(Cliente::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function detalles(): HasMany { return $this->hasMany(NotaCreditoDetalle::class); }
}
