<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega constraints de unicidad e índices para garantizar integridad
 * de datos bajo carga concurrente. Mejoras sobre el sistema viejo que
 * no tenía UNIQUE en clave_acceso ni en identificación de clientes.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Unique en clave_acceso para todos los tipos de comprobante
        // El sistema viejo NO tenía esto, pero el SRI exige claves únicas
        $tablas = [
            'facturas',
            'nota_creditos',
            'nota_debitos',
            'retenciones',
            'guias',
            'liquidacion_compras',
            'retencion_ats',
        ];

        foreach ($tablas as $tabla) {
            Schema::table($tabla, function (Blueprint $table) use ($tabla) {
                // Index en clave_acceso (puede ser null hasta que se genere)
                if (!$this->hasIndex($tabla, 'clave_acceso')) {
                    $table->unique('clave_acceso', "{$tabla}_clave_acceso_unique");
                }

                // Index en numero_autorizacion para búsquedas rápidas
                if (!$this->hasIndex($tabla, 'numero_autorizacion')) {
                    $table->index('numero_autorizacion', "{$tabla}_numero_autorizacion_index");
                }
            });
        }

        // Unique en [emisor_id, identificacion] para clientes
        // Evita duplicar el mismo cliente por emisor
        Schema::table('clientes', function (Blueprint $table) {
            // Eliminar el index simple que ya existe y crear el unique
            $table->unique(['emisor_id', 'identificacion'], 'clientes_emisor_identificacion_unique');
        });

        // Unique en [emisor_id, codigo_principal] para productos
        // Evita duplicar el mismo código de producto por emisor
        Schema::table('productos', function (Blueprint $table) {
            $table->unique(['emisor_id', 'codigo_principal'], 'productos_emisor_codigo_unique');
        });
    }

    public function down(): void
    {
        $tablas = [
            'facturas',
            'nota_creditos',
            'nota_debitos',
            'retenciones',
            'guias',
            'liquidacion_compras',
            'retencion_ats',
        ];

        foreach ($tablas as $tabla) {
            Schema::table($tabla, function (Blueprint $table) use ($tabla) {
                $table->dropUnique("{$tabla}_clave_acceso_unique");
                $table->dropIndex("{$tabla}_numero_autorizacion_index");
            });
        }

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropUnique('clientes_emisor_identificacion_unique');
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->dropUnique('productos_emisor_codigo_unique');
        });
    }

    private function hasIndex(string $table, string $column): bool
    {
        $indexes = Schema::getIndexes($table);
        foreach ($indexes as $index) {
            if (in_array($column, $index['columns'])) {
                return true;
            }
        }
        return false;
    }
};
