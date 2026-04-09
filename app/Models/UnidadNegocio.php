<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class UnidadNegocio extends Model
{
    use HasFactory;

    protected $table = 'unidades_negocio';

    protected $fillable = [
        'emisor_id', 'nombre', 'logo_path', 'activo',
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

    public function establecimientos(): HasMany
    {
        return $this->hasMany(Establecimiento::class);
    }

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class);
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    public function transportistas(): HasMany
    {
        return $this->hasMany(Transportista::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
