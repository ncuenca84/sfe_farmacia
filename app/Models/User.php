<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use OwenIt\Auditing\Contracts\Auditable;

class User extends Authenticatable implements Auditable
{
    use HasFactory, Notifiable;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'username', 'nombre', 'apellido', 'email', 'password',
        'rol_id', 'emisor_id', 'unidad_negocio_id', 'activo',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'rol_id');
    }

    public function emisor(): BelongsTo
    {
        return $this->belongsTo(Emisor::class);
    }

    public function unidadNegocio(): BelongsTo
    {
        return $this->belongsTo(UnidadNegocio::class);
    }

    public function esAdmin(): bool
    {
        return $this->rol->nombre === 'ROLE_ADMIN';
    }

    public function esEmisorAdmin(): bool
    {
        return $this->rol->nombre === 'ROLE_EMISOR_ADMIN';
    }

    public function esEmisor(): bool
    {
        return $this->rol->nombre === 'ROLE_EMISOR';
    }

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombre} {$this->apellido}";
    }

    /**
     * Scope query de clientes/productos/transportistas según la unidad de negocio del usuario.
     * Si tiene unidad_negocio_id asignada, filtra por esa unidad.
     * Si no (null), devuelve todas las del emisor.
     */
    public function scopeUnidadNegocio($query, string $column = 'unidad_negocio_id')
    {
        if ($this->unidad_negocio_id) {
            return $query->where($column, $this->unidad_negocio_id);
        }

        return $query->where('emisor_id', $this->emisor_id);
    }

    /**
     * Obtiene los establecimientos activos del usuario, filtrados por unidad de negocio si aplica.
     */
    public function establecimientosActivos()
    {
        $query = $this->emisor->establecimientos()->with('ptoEmisiones')->where('activo', true);

        if ($this->unidad_negocio_id) {
            $query->where('unidad_negocio_id', $this->unidad_negocio_id);
        }

        return $query->get();
    }
}
