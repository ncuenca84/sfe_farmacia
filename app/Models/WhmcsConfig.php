<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhmcsConfig extends Model
{
    protected $table = 'whmcs_config';

    protected $fillable = ['api_key', 'whmcs_url', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
}
