<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['facturas', 'nota_creditos', 'nota_debitos', 'guias', 'liquidacion_compras'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->text('observaciones')->nullable();
            });
        }
    }

    public function down(): void
    {
        $tables = ['facturas', 'nota_creditos', 'nota_debitos', 'guias', 'liquidacion_compras'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('observaciones');
            });
        }
    }
};
