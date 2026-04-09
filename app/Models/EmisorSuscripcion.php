<?php

namespace App\Models;

use App\Enums\EstadoSuscripcion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmisorSuscripcion extends Model
{
    protected $table = 'emisor_suscripciones';

    protected $fillable = [
        'emisor_id', 'plan_id', 'fecha_inicio', 'fecha_fin',
        'comprobantes_usados', 'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'estado' => EstadoSuscripcion::class,
        ];
    }

    public function emisor(): BelongsTo
    {
        return $this->belongsTo(Emisor::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function diasRestantes(): int
    {
        return max(0, (int) now()->diffInDays($this->fecha_fin, false));
    }

    public function comprobantesRestantes(): int
    {
        if ($this->plan->esIlimitado()) {
            return PHP_INT_MAX;
        }
        return max(0, $this->plan->cant_comprobante - $this->comprobantes_usados);
    }
}
