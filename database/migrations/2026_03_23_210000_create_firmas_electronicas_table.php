<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('firmas_electronicas', function (Blueprint $table) {
            $table->id();
            $table->string('identificacion', 20)->index();
            $table->string('nombres', 150);
            $table->string('apellidos', 150);
            $table->string('celular', 20)->nullable();
            $table->string('correo', 200)->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->string('archivo_p12')->nullable();
            $table->text('password_p12')->nullable();
            $table->string('emisor_cn', 300)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->string('organizacion', 300)->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('emisor_id')->nullable()->constrained('emisores')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('firmas_electronicas');
    }
};
