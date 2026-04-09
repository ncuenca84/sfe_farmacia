<?php

namespace App\Models;

use App\Models\Traits\TieneComprobanteSri;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class LiquidacionCompra extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use TieneComprobanteSri;

    protected $fillable = [
        'emisor_id', 'establecimiento_id', 'pto_emision_id', 'cliente_id',
        'secuencial', 'fecha_emision',
        'total_sin_impuestos', 'total_descuento', 'total_iva', 'total_ice',
        'importe_total', 'moneda',
        'forma_pago', 'forma_pago_valor', 'forma_pago_plazo', 'forma_pago_unidad_tiempo',
        'es_reembolso', 'cod_doc_reembolso',
        'clave_acceso', 'numero_autorizacion', 'fecha_autorizacion',
        'estado', 'ambiente', 'motivo_rechazo', 'xml_path', 'user_id', 'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'fecha_autorizacion' => 'datetime',
            'total_sin_impuestos' => 'decimal:2',
            'importe_total' => 'decimal:2',
            'es_reembolso' => 'boolean',
        ];
    }

    public function emisor(): BelongsTo { return $this->belongsTo(Emisor::class); }
    public function establecimiento(): BelongsTo { return $this->belongsTo(Establecimiento::class); }
    public function ptoEmision(): BelongsTo { return $this->belongsTo(PtoEmision::class); }
    public function cliente(): BelongsTo { return $this->belongsTo(Cliente::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function detalles(): HasMany { return $this->hasMany(LiquidacionDetalle::class); }
    public function reembolsos(): HasMany { return $this->hasMany(LiquidacionReembolso::class); }
}
