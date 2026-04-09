<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique(); // ROLE_ADMIN, ROLE_EMISOR_ADMIN, ROLE_EMISOR
            $table->string('descripcion', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('planes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->integer('cant_comprobante')->default(0); // 0 = ilimitado
            $table->enum('tipo_periodo', ['MENSUAL', 'ANUAL', 'DIAS']);
            $table->integer('dias')->nullable();
            $table->decimal('precio', 10, 2);
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->integer('whmcs_package_id')->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('emisores', function (Blueprint $table) {
            $table->id();
            $table->string('ruc', 13)->unique();
            $table->string('razon_social', 300);
            $table->string('nombre_comercial', 300)->nullable();
            $table->text('direccion_matriz')->nullable();
            $table->enum('ambiente', ['1', '2'])->default('1'); // 1=pruebas, 2=produccion
            $table->enum('tipo_emision', ['1'])->default('1'); // 1=normal
            $table->boolean('obligado_contabilidad')->default(false);
            $table->string('contribuyente_especial', 20)->nullable();
            $table->string('agente_retencion', 10)->nullable();
            $table->boolean('regimen_rimpe')->default(false);
            $table->string('regimen_rimpe_texto', 255)->nullable();
            $table->char('codigo_numerico', 8)->default('00000001');
            $table->string('dir_doc_autorizados', 200)->nullable();
            $table->string('dir_proformas', 200)->nullable();
            $table->string('dir_plantilla_jasper', 200)->nullable();
            // Firma electrónica
            $table->string('firma_path', 255)->nullable();
            $table->text('firma_password')->nullable(); // cifrada
            $table->date('firma_vigencia')->nullable();
            // Correo
            $table->string('mail_host', 100)->nullable();
            $table->integer('mail_port')->nullable();
            $table->string('mail_username', 150)->nullable();
            $table->text('mail_password')->nullable(); // cifrada
            $table->string('mail_encryption', 10)->nullable(); // ssl, tls
            $table->string('mail_from_address', 150)->nullable();
            $table->string('mail_from_name', 150)->nullable();
            // Logo
            $table->string('logo_path', 255)->nullable();
            // Estado
            $table->boolean('activo')->default(true);
            $table->enum('origen', ['MANUAL', 'WHMCS'])->default('MANUAL');
            $table->integer('whmcs_service_id')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 100)->unique();
            $table->string('nombre', 150);
            $table->string('apellido', 150);
            $table->string('email', 200);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->foreignId('rol_id')->constrained('roles');
            $table->foreignId('emisor_id')->nullable()->constrained('emisores')->nullOnDelete();
            $table->boolean('activo')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('emisores');
        Schema::dropIfExists('planes');
        Schema::dropIfExists('roles');
    }
};
