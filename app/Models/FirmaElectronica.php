<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FirmaElectronica extends Model
{
    protected $table = 'firmas_electronicas';

    protected $fillable = [
        'identificacion', 'nombres', 'apellidos', 'celular', 'correo',
        'fecha_inicio', 'fecha_fin', 'archivo_p12', 'password_p12',
        'emisor_cn', 'serial_number', 'organizacion', 'observaciones',
        'emisor_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'password_p12' => 'encrypted',
        ];
    }

    public function emisor(): BelongsTo
    {
        return $this->belongsTo(Emisor::class);
    }

    public function nombreCompleto(): string
    {
        return $this->nombres . ' ' . $this->apellidos;
    }

    public function diasRestantes(): int
    {
        if (!$this->fecha_fin) {
            return 0;
        }
        return max(0, (int) now()->diffInDays($this->fecha_fin, false));
    }

    public function estaVencida(): bool
    {
        return $this->fecha_fin && $this->fecha_fin < now();
    }

    public function estadoTexto(): string
    {
        if (!$this->fecha_fin) return 'Sin fecha';
        if ($this->estaVencida()) return 'Vencida';
        if ($this->diasRestantes() <= 30) return 'Por vencer';
        return 'Vigente';
    }
}
