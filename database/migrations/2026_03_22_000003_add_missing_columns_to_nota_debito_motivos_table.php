<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('nota_debito_motivos')) {
            return;
        }

        Schema::table('nota_debito_motivos', function (Blueprint $table) {
            if (!Schema::hasColumn('nota_debito_motivos', 'impuesto_iva_id')) {
                $table->unsignedBigInteger('impuesto_iva_id')->nullable()->after('valor');
                $table->foreign('impuesto_iva_id')->references('id')->on('impuesto_ivas')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('nota_debito_motivos', 'impuesto_iva_id')) {
            Schema::table('nota_debito_motivos', function (Blueprint $table) {
                $table->dropForeign(['impuesto_iva_id']);
                $table->dropColumn('impuesto_iva_id');
            });
        }
    }
};
