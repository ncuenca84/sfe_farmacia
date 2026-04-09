<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Establecimiento extends Model
{
    protected $fillable = [
        'emisor_id', 'unidad_negocio_id', 'codigo', 'nombre', 'direccion', 'logo_path', 'activo', 'maneja_inventario',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'maneja_inventario' => 'boolean',
        ];
    }

    public function emisor(): BelongsTo
    {
        return $this->belongsTo(Emisor::class);
    }

    public function unidadNegocio(): BelongsTo
    {
        return $this->belongsTo(UnidadNegocio::class);
    }

    public function ptoEmisiones(): HasMany
    {
        return $this->hasMany(PtoEmision::class);
    }
}
