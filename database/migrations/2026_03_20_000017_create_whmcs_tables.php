<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whmcs_config', function (Blueprint $table) {
            $table->id();
            $table->string('api_key', 64)->unique();
            $table->string('whmcs_url', 255);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('whmcs_servicios', function (Blueprint $table) {
            $table->id();
            $table->integer('whmcs_service_id')->unique();
            $table->integer('whmcs_client_id');
            $table->integer('whmcs_package_id');
            $table->foreignId('emisor_id')->constrained('emisores');
            $table->string('estado', 20)->default('ACTIVO');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whmcs_servicios');
        Schema::dropIfExists('whmcs_config');
    }
};
