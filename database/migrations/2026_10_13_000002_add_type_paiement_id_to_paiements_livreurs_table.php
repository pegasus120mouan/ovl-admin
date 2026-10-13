<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('paiements_livreurs')) {
            return;
        }

        Schema::table('paiements_livreurs', function (Blueprint $table) {
            if (!Schema::hasColumn('paiements_livreurs', 'type_paiement_id')) {
                // Référence vers type_paiement.id (Orange, MTN, Wave, Djamo...).
                $table->unsignedBigInteger('type_paiement_id')->nullable()->after('montant_valide');
                $table->index('type_paiement_id', 'paiements_livreurs_type_paiement_idx');
            }
        });

        // mode_paiement conserve le nom de l'opérateur au moment de la déclaration :
        // on élargit la colonne car les opérateurs viennent désormais de la table type_paiement.
        Schema::table('paiements_livreurs', function (Blueprint $table) {
            $table->string('mode_paiement', 100)->change();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('paiements_livreurs')) {
            return;
        }

        Schema::table('paiements_livreurs', function (Blueprint $table) {
            if (Schema::hasColumn('paiements_livreurs', 'type_paiement_id')) {
                $table->dropIndex('paiements_livreurs_type_paiement_idx');
                $table->dropColumn('type_paiement_id');
            }
        });
    }
};
