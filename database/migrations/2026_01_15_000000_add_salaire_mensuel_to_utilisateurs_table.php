<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('utilisateurs')) {
            return;
        }

        Schema::table('utilisateurs', function (Blueprint $table) {
            if (!Schema::hasColumn('utilisateurs', 'salaire_mensuel')) {
                $table->unsignedBigInteger('salaire_mensuel')->nullable()->after('statut_compte');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('utilisateurs')) {
            return;
        }

        Schema::table('utilisateurs', function (Blueprint $table) {
            if (Schema::hasColumn('utilisateurs', 'salaire_mensuel')) {
                $table->dropColumn('salaire_mensuel');
            }
        });
    }
};
