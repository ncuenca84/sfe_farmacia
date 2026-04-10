<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Presentaciones (tableta, jarabe, inyectable, crema, etc.)
        Schema::create('presentaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emisor_id')->constrained('emisores')->cascadeOnDelete();
            $table->string('nombre', 100);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['emisor_id', 'nombre']);
        });

        // Laboratorios fabricantes
        Schema::create('laboratorios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emisor_id')->constrained('emisores')->cascadeOnDelete();
            $table->string('nombre', 200);
            $table->string('pais', 100)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['emisor_id', 'nombre']);
        });

        // Campos farmacéuticos en productos
        Schema::table('productos', function (Blueprint $table) {
            $table->string('principio_activo', 300)->nullable()->after('descripcion');
            $table->string('concentracion', 100)->nullable()->after('principio_activo');
            $table->foreignId('presentacion_id')->nullable()->after('concentracion')
                ->constrained('presentaciones')->nullOnDelete();
            $table->foreignId('laboratorio_id')->nullable()->after('presentacion_id')
                ->constrained('laboratorios')->nullOnDelete();
            $table->string('tipo_venta', 30)->default('venta_libre')->after('laboratorio_id');
            $table->string('registro_sanitario', 50)->nullable()->after('tipo_venta');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('presentacion_id');
            $table->dropConstrainedForeignId('laboratorio_id');
            $table->dropColumn(['principio_activo', 'concentracion', 'tipo_venta', 'registro_sanitario']);
        });

        Schema::dropIfExists('laboratorios');
        Schema::dropIfExists('presentaciones');
    }
};
