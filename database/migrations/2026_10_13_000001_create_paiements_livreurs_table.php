<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('paiements_livreurs')) {
            return;
        }

        Schema::create('paiements_livreurs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('utilisateur_id');
            // Jour de versement concerné (même clé que points_livreurs.date_commande).
            $table->date('date_versement');
            $table->unsignedBigInteger('montant');
            $table->unsignedBigInteger('montant_valide')->nullable();
            // OM, Wave, Moov Money
            $table->string('mode_paiement', 20);
            $table->string('reference', 100)->nullable();
            // En attente, Validé, Rejeté
            $table->string('statut', 20)->default('En attente');
            $table->string('motif_rejet', 255)->nullable();
            $table->dateTime('traite_le')->nullable();
            $table->timestamps();

            $table->index(['utilisateur_id', 'date_versement', 'statut'], 'paiements_livreurs_user_date_statut_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements_livreurs');
    }
};
