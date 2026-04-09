<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmNota extends Model
{
    protected $table = 'crm_notas';

    protected $fillable = ['emisor_id', 'contenido', 'creado_por'];

    public function emisor(): BelongsTo
    {
        return $this->belongsTo(Emisor::class);
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }
}
