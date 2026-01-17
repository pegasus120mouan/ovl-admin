<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facture_lignes', function (Blueprint $table) {
            if (Schema::hasColumn('facture_lignes', 'statut')) {
                $table->dropColumn('statut');
            }
        });
    }

    public function down(): void
    {
        Schema::table('facture_lignes', function (Blueprint $table) {
            if (!Schema::hasColumn('facture_lignes', 'statut')) {
                $table->string('statut')->nullable()->after('prix_total');
            }
        });
    }
};
