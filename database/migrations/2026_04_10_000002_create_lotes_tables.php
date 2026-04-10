<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emisor_id')->constrained('emisores')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('establecimiento_id')->constrained('establecimientos')->cascadeOnDelete();
            $table->string('numero_lote', 100);
            $table->date('fecha_vencimiento')->nullable();
            $table->decimal('cantidad_inicial', 14, 4)->default(0);
            $table->decimal('cantidad_actual', 14, 4)->default(0);
            $table->decimal('costo_unitario', 14, 4)->default(0);
            $table->date('fecha_ingreso');
            $table->string('nota', 300)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['producto_id', 'establecimiento_id', 'activo']);
            $table->index(['establecimiento_id', 'fecha_vencimiento']);
        });

        Schema::create('lote_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_id')->constrained('lotes')->cascadeOnDelete();
            $table->string('tipo', 20); // ENTRADA, SALIDA, AJUSTE
            $table->decimal('cantidad', 14, 4);
            $table->decimal('cantidad_anterior', 14, 4);
            $table->decimal('cantidad_posterior', 14, 4);
            $table->string('referencia_type')->nullable();
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->string('descripcion', 300)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['referencia_type', 'referencia_id']);
            $table->index('lote_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lote_movimientos');
        Schema::dropIfExists('lotes');
    }
};
