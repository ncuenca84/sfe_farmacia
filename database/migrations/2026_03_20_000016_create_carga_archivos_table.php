<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carga_archivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emisor_id')->constrained('emisores');
            $table->string('tipo', 50); // FACTURAS, RETENCIONES, etc
            $table->string('dir_archivo', 255);
            $table->string('estado', 20)->default('CARGADO'); // CARGADO, PROCESANDO, COMPLETADO, ERROR
            $table->integer('total_registros')->default(0);
            $table->integer('procesados')->default(0);
            $table->integer('errores')->default(0);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('carga_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carga_archivo_id')->constrained('carga_archivos')->cascadeOnDelete();
            $table->integer('fila')->nullable();
            $table->text('mensaje');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carga_errors');
        Schema::dropIfExists('carga_archivos');
    }
};
