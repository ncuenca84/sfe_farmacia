<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nota_debitos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emisor_id')->constrained('emisores');
            $table->foreignId('establecimiento_id')->constrained('establecimientos');
            $table->foreignId('pto_emision_id')->constrained('pto_emisiones');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->integer('secuencial');
            $table->date('fecha_emision');
            // Documento modificado
            $table->string('cod_doc_modificado', 2);
            $table->string('num_doc_modificado', 17);
            $table->date('fecha_emision_doc_sustento');
            // Totales
            $table->decimal('total_sin_impuestos', 14, 2)->default(0);
            $table->decimal('total_descuento', 14, 2)->default(0);
            $table->decimal('total_iva', 14, 2)->default(0);
            $table->decimal('total_ice', 14, 2)->default(0);
            $table->decimal('importe_total', 14, 2)->default(0);
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

        Schema::create('nota_debito_motivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nota_debito_id')->constrained('nota_debitos')->cascadeOnDelete();
            $table->string('razon', 300);
            $table->decimal('valor', 14, 2);
            $table->foreignId('impuesto_iva_id')->nullable()->constrained('impuesto_ivas')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nota_debito_motivos');
        Schema::dropIfExists('nota_debitos');
    }
};
