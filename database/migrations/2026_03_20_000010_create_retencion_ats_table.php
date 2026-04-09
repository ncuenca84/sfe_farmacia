<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retencion_ats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emisor_id')->constrained('emisores');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->string('periodo_fiscal', 7)->nullable(); // MM/YYYY
            $table->string('estado', 20)->default('CREADA');

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('doc_sustento_retenciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retencion_ats_id')->constrained('retencion_ats')->cascadeOnDelete();
            $table->string('cod_doc_sustento', 2);
            $table->string('num_doc_sustento', 17);
            $table->date('fecha_emision_doc_sustento');
            $table->string('num_aut_doc_sustento', 49)->nullable();
            $table->decimal('total_sin_impuestos', 14, 2)->default(0);
            $table->decimal('total_iva', 14, 2)->default(0);
            $table->decimal('importe_total', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('desgloce_retenciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doc_sustento_retencion_id')->constrained('doc_sustento_retenciones')->cascadeOnDelete();
            $table->string('codigo_impuesto', 4);
            $table->string('codigo_retencion', 10);
            $table->decimal('base_imponible', 14, 2);
            $table->decimal('porcentaje_retener', 5, 2);
            $table->decimal('valor_retenido', 14, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('desgloce_retenciones');
        Schema::dropIfExists('doc_sustento_retenciones');
        Schema::dropIfExists('retencion_ats');
    }
};
