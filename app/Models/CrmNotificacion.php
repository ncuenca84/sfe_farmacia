<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrmNotificacion extends Model
{
    protected $table = 'crm_notificaciones';

    protected $fillable = [
        'asunto', 'mensaje', 'tipo', 'estado', 'destinatarios',
        'emisor_ids', 'enviados', 'fallidos', 'enviada_at', 'creado_por',
    ];

    protected function casts(): array
    {
        return [
            'emisor_ids' => 'array',
            'enviada_at' => 'datetime',
        ];
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function historialEmails(): HasMany
    {
        return $this->hasMany(CrmHistorialEmail::class, 'notificacion_id');
    }
}
