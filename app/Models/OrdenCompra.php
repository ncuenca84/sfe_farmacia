<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrdenCompra extends Model
{
    protected $table = 'ordenes_compra';

    protected $fillable = [
        'emisor_id', 'proveedor_id', 'establecimiento_id',
        'numero', 'fecha', 'estado', 'total', 'observaciones', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'total' => 'decimal:4',
        ];
    }

    public function emisor(): BelongsTo { return $this->belongsTo(Emisor::class); }
    public function proveedor(): BelongsTo { return $this->belongsTo(Proveedor::class); }
    public function establecimiento(): BelongsTo { return $this->belongsTo(Establecimiento::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function items(): HasMany
    {
        return $this->hasMany(OrdenCompraItem::class);
    }

    public function estaCompleta(): bool
    {
        return $this->items->every(fn ($i) => $i->cantidad_recibida >= $i->cantidad_pedida);
    }
}
