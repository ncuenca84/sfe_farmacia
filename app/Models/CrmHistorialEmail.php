<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmHistorialEmail extends Model
{
    protected $table = 'crm_historial_emails';

    protected $fillable = [
        'emisor_id', 'notificacion_id', 'email_destino',
        'asunto', 'estado', 'error',
    ];

    public function emisor(): BelongsTo
    {
        return $this->belongsTo(Emisor::class);
    }

    public function notificacion(): BelongsTo
    {
        return $this->belongsTo(CrmNotificacion::class, 'notificacion_id');
    }
}
