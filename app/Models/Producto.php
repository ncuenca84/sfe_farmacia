<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    protected $fillable = [
        'emisor_id', 'unidad_negocio_id', 'categoria_producto_id', 'proveedor_id',
        'codigo_principal', 'codigo_auxiliar',
        'nombre', 'descripcion', 'principio_activo', 'concentracion',
        'presentacion_id', 'laboratorio_id', 'tipo_venta', 'registro_sanitario',
        'numero_lote', 'fecha_vencimiento', 'imagen',
        'precio_unitario',
        'impuesto_iva_id', 'tiene_ice', 'impuesto_ice_id', 'activo',
    ];

    protected function casts(): array
    {
        return [
            'precio_unitario' => 'decimal:6',
            'tiene_ice' => 'boolean',
            'activo' => 'boolean',
            'fecha_vencimiento' => 'date',
        ];
    }

    public function categoriaProducto(): BelongsTo
    {
        return $this->belongsTo(CategoriaProducto::class);
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function presentacion(): BelongsTo
    {
        return $this->belongsTo(Presentacion::class);
    }

    public function laboratorio(): BelongsTo
    {
        return $this->belongsTo(Laboratorio::class);
    }

    public function requiereReceta(): bool
    {
        return in_array($this->tipo_venta, ['requiere_receta', 'controlado']);
    }

    public function esControlado(): bool
    {
        return $this->tipo_venta === 'controlado';
    }

    public function estaVencido(): bool
    {
        return $this->fecha_vencimiento && $this->fecha_vencimiento->isPast();
    }

    public function proximoAVencer(int $dias = 30): bool
    {
        return $this->fecha_vencimiento
            && !$this->estaVencido()
            && $this->fecha_vencimiento->lte(now()->addDays($dias));
    }

    public function emisor(): BelongsTo
    {
        return $this->belongsTo(Emisor::class);
    }

    public function unidadNegocio(): BelongsTo
    {
        return $this->belongsTo(UnidadNegocio::class);
    }

    public function impuestoIva(): BelongsTo
    {
        return $this->belongsTo(ImpuestoIva::class);
    }

    public function impuestoIce(): BelongsTo
    {
        return $this->belongsTo(ImpuestoIce::class);
    }

    public function inventarios(): HasMany
    {
        return $this->hasMany(Inventario::class);
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class);
    }
}
