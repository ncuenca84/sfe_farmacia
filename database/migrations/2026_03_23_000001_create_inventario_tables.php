<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Flag para activar inventario por emisor
        if (!Schema::hasColumn('emisores', 'maneja_inventario')) {
            Schema::table('emisores', function (Blueprint $table) {
                $table->boolean('maneja_inventario')->default(false)->after('activo');
            });
        }

        // Limpiar tablas de ejecuciones fallidas previas
        Schema::dropIfExists('movimiento_inventarios');
        Schema::dropIfExists('inventarios');
        Schema::create('inventarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emisor_id')->constrained('emisores');
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('establecimiento_id')->constrained('establecimientos');
            $table->decimal('stock_actual', 14, 4)->default(0);
            $table->decimal('stock_minimo', 14, 4)->default(0);
            $table->decimal('costo_promedio', 14, 4)->default(0);
            $table->timestamps();

            $table->unique(['producto_id', 'establecimiento_id']);
            $table->index(['emisor_id', 'establecimiento_id']);
        });

        // Historial de movimientos (kardex)
        Schema::create('movimiento_inventarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emisor_id')->constrained('emisores');
            $table->foreignId('inventario_id')->constrained('inventarios');
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('establecimiento_id')->constrained('establecimientos');
            $table->string('tipo', 20); // ENTRADA, SALIDA, AJUSTE, TRANSFERENCIA
            $table->decimal('cantidad', 14, 4);
            $table->decimal('costo_unitario', 14, 4)->default(0);
            $table->decimal('costo_total', 14, 4)->default(0);
            $table->decimal('stock_resultante', 14, 4);
            $table->string('referencia_type')->nullable(); // Polymorphic: Factura, NotaCredito, etc.
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->string('descripcion', 500)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['referencia_type', 'referencia_id']);
            $table->index(['producto_id', 'establecimiento_id', 'created_at'], 'mov_inv_prod_est_fecha_idx');
            $table->index('emisor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimiento_inventarios');
        Schema::dropIfExists('inventarios');

        Schema::table('emisores', function (Blueprint $table) {
            $table->dropColumn('maneja_inventario');
        });
    }
};
