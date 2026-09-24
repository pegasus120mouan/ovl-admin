<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bordereaux_commissions', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 40)->unique();
            $table->integer('commercial_id');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->dateTime('genere_le');
            $table->unsignedInteger('nb_colis')->default(0);
            $table->integer('base_livraison')->default(0);
            $table->integer('montant')->default(0);

            $table->foreign('commercial_id')->references('id')->on('utilisateurs');
        });

        Schema::create('bordereau_commission_colis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bordereau_id');
            $table->integer('commande_id')->unique();
            $table->integer('montant')->default(0);

            $table->foreign('bordereau_id')->references('id')->on('bordereaux_commissions')->cascadeOnDelete();
            $table->foreign('commande_id')->references('id')->on('commandes');
        });

        Schema::table('paiements_commissions', function (Blueprint $table) {
            $table->dropForeign(['commercial_id']);
        });

        Schema::table('paiements_commissions', function (Blueprint $table) {
            $table->dropUnique(['commercial_id', 'periode']);
            $table->unsignedBigInteger('bordereau_id')->nullable()->after('commercial_id');
            $table->string('mode')->nullable()->after('montant');
            $table->string('statut')->default('Validé')->after('mode');
            $table->string('recu')->nullable()->after('statut');
            $table->foreign('commercial_id')->references('id')->on('utilisateurs');
            $table->foreign('bordereau_id')->references('id')->on('bordereaux_commissions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('paiements_commissions', function (Blueprint $table) {
            $table->dropForeign(['bordereau_id']);
            $table->dropColumn(['bordereau_id', 'mode', 'statut', 'recu']);
            $table->unique(['commercial_id', 'periode']);
        });

        Schema::dropIfExists('bordereau_commission_colis');
        Schema::dropIfExists('bordereaux_commissions');
    }
};
