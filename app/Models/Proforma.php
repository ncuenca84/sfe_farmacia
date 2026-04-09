<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proforma extends Model
{
    protected $fillable = [
        'emisor_id', 'establecimiento_id', 'pto_emision_id', 'cliente_id',
        'secuencial', 'fecha_emision', 'fecha_vencimiento', 'guia_remision',
        'total_sin_impuestos', 'total_descuento', 'total_iva', 'total_ice',
        'importe_total', 'moneda',
        'forma_pago', 'forma_pago_valor', 'forma_pago_plazo', 'forma_pago_unidad_tiempo',
        'estado', 'observaciones', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'fecha_vencimiento' => 'date',
            'total_sin_impuestos' => 'decimal:2',
            'importe_total' => 'decimal:2',
        ];
    }

    public function emisor(): BelongsTo { return $this->belongsTo(Emisor::class); }
    public function establecimiento(): BelongsTo { return $this->belongsTo(Establecimiento::class); }
    public function ptoEmision(): BelongsTo { return $this->belongsTo(PtoEmision::class); }
    public function cliente(): BelongsTo { return $this->belongsTo(Cliente::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function detalles(): HasMany { return $this->hasMany(ProformaDetalle::class); }

    public function getNumeroCompletoAttribute(): string
    {
        $est = $this->establecimiento->codigo ?? '000';
        $pto = $this->ptoEmision->codigo ?? '000';
        $sec = str_pad($this->secuencial, 9, '0', STR_PAD_LEFT);
        return "{$est}-{$pto}-{$sec}";
    }
}
