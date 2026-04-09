<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_notificaciones', function (Blueprint $table) {
            $table->id();
            $table->string('asunto', 255);
            $table->longText('mensaje');
            $table->enum('tipo', ['MANUAL', 'PROMOCION', 'ALERTA_PLAN', 'ALERTA_FIRMA', 'SUSPENSION', 'GENERAL'])->default('MANUAL');
            $table->enum('estado', ['PENDIENTE', 'ENVIADA', 'FALLIDA'])->default('PENDIENTE');
            $table->enum('destinatarios', ['TODOS', 'ACTIVOS', 'INACTIVOS', 'VENCIDOS', 'SELECCIONADOS'])->default('TODOS');
            $table->json('emisor_ids')->nullable();
            $table->unsignedInteger('enviados')->default(0);
            $table->unsignedInteger('fallidos')->default(0);
            $table->timestamp('enviada_at')->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('crm_historial_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emisor_id')->constrained('emisores')->cascadeOnDelete();
            $table->foreignId('notificacion_id')->nullable()->constrained('crm_notificaciones')->nullOnDelete();
            $table->string('email_destino', 200);
            $table->string('asunto', 255);
            $table->enum('estado', ['ENVIADO', 'FALLIDO'])->default('ENVIADO');
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['emisor_id', 'created_at']);
        });

        Schema::create('crm_notas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emisor_id')->constrained('emisores')->cascadeOnDelete();
            $table->text('contenido');
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('emisor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_notas');
        Schema::dropIfExists('crm_historial_emails');
        Schema::dropIfExists('crm_notificaciones');
    }
};
