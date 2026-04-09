<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    protected $table = 'proveedores';

    protected $fillable = [
        'emisor_id', 'identificacion', 'nombre', 'direccion',
        'telefono', 'email', 'contacto', 'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function emisor(): BelongsTo
    {
        return $this->belongsTo(Emisor::class);
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }
}
