<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emisor_suscripciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emisor_id')->constrained('emisores');
            $table->foreignId('plan_id')->constrained('planes');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->integer('comprobantes_usados')->default(0);
            $table->enum('estado', ['ACTIVA', 'VENCIDA', 'SUSPENDIDA'])->default('ACTIVA');
            $table->timestamps();

            $table->index(['emisor_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emisor_suscripciones');
    }
};
