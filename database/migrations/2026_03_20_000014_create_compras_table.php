<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emisor_id')->constrained('emisores');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->string('tipo_comprobante', 2);
            $table->string('numero_comprobante', 17);
            $table->string('autorizacion', 49)->nullable();
            $table->date('fecha_emision');
            $table->decimal('total_sin_impuestos', 14, 2)->default(0);
            $table->decimal('total_iva', 14, 2)->default(0);
            $table->decimal('importe_total', 14, 2)->default(0);
            $table->string('estado', 20)->default('CREADA');

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['emisor_id', 'fecha_emision']);
        });

        Schema::create('compra_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compra_id')->constrained('compras')->cascadeOnDelete();
            $table->string('descripcion', 300);
            $table->decimal('cantidad', 14, 4);
            $table->decimal('precio_unitario', 14, 4);
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('iva', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compra_detalles');
        Schema::dropIfExists('compras');
    }
};
