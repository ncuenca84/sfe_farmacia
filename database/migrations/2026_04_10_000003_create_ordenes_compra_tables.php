<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordenes_compra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emisor_id')->constrained('emisores')->cascadeOnDelete();
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->foreignId('establecimiento_id')->constrained('establecimientos');
            $table->string('numero', 30)->nullable();
            $table->date('fecha');
            $table->string('estado', 20)->default('PENDIENTE'); // PENDIENTE, PARCIAL, RECIBIDA, CANCELADA
            $table->decimal('total', 14, 4)->default(0);
            $table->text('observaciones')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['emisor_id', 'estado']);
        });

        Schema::create('orden_compra_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_compra_id')->constrained('ordenes_compra')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->decimal('cantidad_pedida', 14, 4);
            $table->decimal('cantidad_recibida', 14, 4)->default(0);
            $table->decimal('costo_unitario', 14, 4)->default(0);
            $table->string('numero_lote', 100)->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orden_compra_items');
        Schema::dropIfExists('ordenes_compra');
    }
};
