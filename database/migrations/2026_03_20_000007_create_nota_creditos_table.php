<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nota_creditos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emisor_id')->constrained('emisores');
            $table->foreignId('establecimiento_id')->constrained('establecimientos');
            $table->foreignId('pto_emision_id')->constrained('pto_emisiones');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->integer('secuencial');
            $table->date('fecha_emision');
            // Documento modificado
            $table->string('cod_doc_modificado', 2); // 01=factura
            $table->string('num_doc_modificado', 17); // 001-001-000000001
            $table->date('fecha_emision_doc_sustento');
            $table->string('motivo', 300);
            // Totales
            $table->decimal('total_sin_impuestos', 14, 2)->default(0);
            $table->decimal('total_descuento', 14, 2)->default(0);
            $table->decimal('total_iva', 14, 2)->default(0);
            $table->decimal('total_ice', 14, 2)->default(0);
            $table->decimal('importe_total', 14, 2)->default(0);
            $table->string('moneda', 15)->default('DOLAR');
            // SRI
            $table->string('clave_acceso', 49)->nullable();
            $table->string('numero_autorizacion', 49)->nullable();
            $table->dateTime('fecha_autorizacion')->nullable();
            $table->string('estado', 20)->default('CREADA');
            $table->text('motivo_rechazo')->nullable();
            $table->string('xml_path', 255)->nullable();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['emisor_id', 'estado']);
            $table->unique(['pto_emision_id', 'secuencial']);
        });

        Schema::create('nota_credito_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nota_credito_id')->constrained('nota_creditos')->cascadeOnDelete();
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
        Schema::dropIfExists('nota_credito_detalles');
        Schema::dropIfExists('nota_creditos');
    }
};
