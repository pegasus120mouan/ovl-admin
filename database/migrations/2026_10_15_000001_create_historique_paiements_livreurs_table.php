<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('historique_paiements_livreurs')) {
            return;
        }

        Schema::create('historique_paiements_livreurs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('utilisateur_id');
            // Jour de versement concerné (même clé que points_livreurs.date_commande).
            $table->date('date_versement');
            // paiement, annulation
            $table->string('type', 20);
            // manuel, masse, application, reprise
            $table->string('origine', 20);
            $table->unsignedBigInteger('montant');
            $table->unsignedBigInteger('paiement_livreur_id')->nullable();
            $table->unsignedBigInteger('enregistre_par')->nullable();
            // Null pour les paiements repris de points_livreurs (antérieurs au journal).
            $table->timestamps();

            $table->index(['utilisateur_id', 'date_versement'], 'historique_paiements_livreurs_user_date_idx');
        });

        DB::table('historique_paiements_livreurs')->insertUsing(
            ['utilisateur_id', 'date_versement', 'type', 'origine', 'montant'],
            DB::table('points_livreurs')
                ->where('montant_verse', '>', 0)
                ->selectRaw("utilisateur_id, DATE(date_commande), 'paiement', 'reprise', montant_verse")
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('historique_paiements_livreurs');
    }
};
