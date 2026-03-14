<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reclamations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('commande_id');
            $table->unsignedBigInteger('utilisateur_id');
            $table->string('type_reclamation', 50); // montant_incorrect, commune_incorrecte, statut_incorrect, autre
            $table->integer('montant_actuel')->nullable();
            $table->integer('montant_reclame')->nullable();
            $table->enum('statut', ['en_attente', 'acceptee', 'refusee'])->default('en_attente');
            $table->text('reponse_admin')->nullable();
            $table->timestamp('date_traitement')->nullable();
            $table->timestamps();

            $table->index('commande_id');
            $table->index('utilisateur_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reclamations');
    }
};
