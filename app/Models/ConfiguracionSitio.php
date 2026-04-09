<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ConfiguracionSitio extends Model
{
    protected $table = 'configuraciones_sitio';

    protected $fillable = ['clave', 'valor'];

    public static function obtener(string $clave, ?string $default = null): ?string
    {
        $valor = Cache::remember("config_sitio.{$clave}", 3600, function () use ($clave) {
            return static::where('clave', $clave)->value('valor');
        });

        return ($valor !== null && $valor !== '') ? $valor : $default;
    }

    public static function guardar(string $clave, ?string $valor): void
    {
        static::updateOrCreate(['clave' => $clave], ['valor' => $valor]);
        Cache::forget("config_sitio.{$clave}");
    }

    public static function nombreSitio(): string
    {
        return static::obtener('nombre_sitio') ?: config('app.name', 'SistemSFE');
    }
}
