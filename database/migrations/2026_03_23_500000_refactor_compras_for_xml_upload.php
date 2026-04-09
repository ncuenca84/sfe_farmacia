<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agregar columnas necesarias para soportar la carga de compras
     * desde archivos XML del SRI.
     */
    public function up(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->string('clave_acceso', 49)->nullable()->after('autorizacion');
            $table->longText('xml_contenido')->nullable();
            $table->string('ruc_proveedor', 13)->nullable()->after('cliente_id');
            $table->string('razon_social_proveedor', 300)->nullable()->after('ruc_proveedor');
            $table->foreignId('establecimiento_id')->nullable()->after('emisor_id')
                ->constrained('establecimientos');
        });

        Schema::table('compra_detalles', function (Blueprint $table) {
            $table->string('codigo_principal', 50)->nullable()->after('compra_id');
            $table->string('codigo_auxiliar', 50)->nullable()->after('codigo_principal');
            $table->foreignId('producto_id')->nullable()->after('codigo_auxiliar')
                ->constrained('productos')->nullOnDelete();
            $table->boolean('agregar_inventario')->default(false)->after('total');
        });
    }

    public function down(): void
    {
        Schema::table('compra_detalles', function (Blueprint $table) {
            $table->dropForeign(['producto_id']);
            $table->dropColumn([
                'codigo_principal',
                'codigo_auxiliar',
                'producto_id',
                'agregar_inventario',
            ]);
        });

        Schema::table('compras', function (Blueprint $table) {
            $table->dropForeign(['establecimiento_id']);
            $table->dropColumn([
                'clave_acceso',
                'xml_contenido',
                'ruc_proveedor',
                'razon_social_proveedor',
                'establecimiento_id',
            ]);
        });
    }
};
