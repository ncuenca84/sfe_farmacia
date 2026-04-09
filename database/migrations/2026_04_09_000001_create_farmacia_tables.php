<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Categorías de productos (medicamentos)
        Schema::create('categorias_producto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emisor_id')->constrained('emisores')->cascadeOnDelete();
            $table->string('nombre', 150);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['emisor_id', 'nombre']);
        });

        // Proveedores
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emisor_id')->constrained('emisores')->cascadeOnDelete();
            $table->string('identificacion', 20)->nullable();
            $table->string('nombre', 300);
            $table->string('direccion', 300)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('contacto', 200)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['emisor_id', 'nombre']);
        });

        // Campos farmacéuticos en productos
        Schema::table('productos', function (Blueprint $table) {
            $table->foreignId('categoria_producto_id')->nullable()->after('unidad_negocio_id')
                ->constrained('categorias_producto')->nullOnDelete();
            $table->foreignId('proveedor_id')->nullable()->after('categoria_producto_id')
                ->constrained('proveedores')->nullOnDelete();
            $table->string('numero_lote', 100)->nullable()->after('descripcion');
            $table->date('fecha_vencimiento')->nullable()->after('numero_lote');
            $table->string('imagen', 500)->nullable()->after('fecha_vencimiento');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('categoria_producto_id');
            $table->dropConstrainedForeignId('proveedor_id');
            $table->dropColumn(['numero_lote', 'fecha_vencimiento', 'imagen']);
        });

        Schema::dropIfExists('proveedores');
        Schema::dropIfExists('categorias_producto');
    }
};
