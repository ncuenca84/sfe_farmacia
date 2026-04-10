<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lote extends Model
{
    protected $fillable = [
        'emisor_id', 'producto_id', 'establecimiento_id',
        'numero_lote', 'fecha_vencimiento', 'cantidad_inicial',
        'cantidad_actual', 'costo_unitario', 'fecha_ingreso', 'nota', 'activo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_vencimiento' => 'date',
            'fecha_ingreso' => 'date',
            'cantidad_inicial' => 'decimal:4',
            'cantidad_actual' => 'decimal:4',
            'costo_unitario' => 'decimal:4',
            'activo' => 'boolean',
        ];
    }

    public function emisor(): BelongsTo
    {
        return $this->belongsTo(Emisor::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function establecimiento(): BelongsTo
    {
        return $this->belongsTo(Establecimiento::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(LoteMovimiento::class);
    }

    public function estaVencido(): bool
    {
        return $this->fecha_vencimiento && $this->fecha_vencimiento->isPast();
    }

    public function tieneStock(): bool
    {
        return $this->cantidad_actual > 0;
    }

    public function scopeConStock($query)
    {
        return $query->where('cantidad_actual', '>', 0)->where('activo', true);
    }

    public function scopeFefo($query)
    {
        return $query->conStock()
            ->orderByRaw('fecha_vencimiento IS NULL ASC')
            ->orderBy('fecha_vencimiento')
            ->orderBy('fecha_ingreso');
    }
}
