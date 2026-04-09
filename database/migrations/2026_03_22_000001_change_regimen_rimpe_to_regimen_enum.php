<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emisores', function (Blueprint $table) {
            $table->string('regimen', 20)->default('GENERAL')->after('agente_retencion');
        });

        // Migrar datos existentes
        DB::table('emisores')->where('regimen_rimpe', true)->update(['regimen' => 'RIMPE']);

        Schema::table('emisores', function (Blueprint $table) {
            $table->dropColumn(['regimen_rimpe', 'regimen_rimpe_texto']);
        });
    }

    public function down(): void
    {
        Schema::table('emisores', function (Blueprint $table) {
            $table->boolean('regimen_rimpe')->default(false)->after('agente_retencion');
            $table->string('regimen_rimpe_texto', 255)->nullable()->after('regimen_rimpe');
        });

        DB::table('emisores')->where('regimen', '!=', 'GENERAL')->update(['regimen_rimpe' => true]);

        Schema::table('emisores', function (Blueprint $table) {
            $table->dropColumn('regimen');
        });
    }
};
