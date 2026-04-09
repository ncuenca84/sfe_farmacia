<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transportistas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emisor_id')->constrained('emisores');
            $table->string('tipo_identificacion', 2)->default('04');
            $table->string('identificacion', 20);
            $table->string('razon_social', 300);
            $table->string('placa', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->timestamps();
            $table->unique(['emisor_id', 'identificacion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transportistas');
    }
};
