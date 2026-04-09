<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proformas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emisor_id')->constrained('emisores');
            $table->foreignId('establecimiento_id')->constrained('establecimientos');
            $table->foreignId('pto_emision_id')->constrained('pto_emisiones');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->integer('secuencial');
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento')->nullable();
            $table->string('guia_remision', 20)->nullable();
            // Totales
            $table->decimal('total_sin_impuestos', 14, 2)->default(0);
            $table->decimal('total_descuento', 14, 2)->default(0);
            $table->decimal('total_iva', 14, 2)->default(0);
            $table->decimal('total_ice', 14, 2)->default(0);
            $table->decimal('importe_total', 14, 2)->default(0);
            $table->string('moneda', 15)->default('DOLAR');
            // Forma de pago
            $table->string('forma_pago', 2)->default('01');
            $table->decimal('forma_pago_valor', 14, 2)->default(0);
            $table->integer('forma_pago_plazo')->default(0);
            $table->string('forma_pago_unidad_tiempo', 20)->default('dias');
            // Estado
            $table->string('estado', 20)->default('CREADA');
            $table->text('observaciones')->nullable();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['emisor_id', 'estado']);
            $table->unique(['pto_emision_id', 'secuencial']);
        });

        Schema::create('proforma_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proforma_id')->constrained('proformas')->cascadeOnDelete();
            $table->string('codigo_principal', 50)->nullable();
            $table->string('codigo_auxiliar', 50)->nullable();
            $table->string('descripcion', 300);
            $table->decimal('cantidad', 14, 4);
            $table->decimal('precio_unitario', 14, 4);
            $table->decimal('descuento', 14, 2)->default(0);
            $table->decimal('precio_total_sin_impuesto', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proforma_detalles');
        Schema::dropIfExists('proformas');
    }
};
