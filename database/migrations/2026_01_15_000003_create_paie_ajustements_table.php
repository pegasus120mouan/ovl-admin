<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paie_ajustements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paie_livreur_id');
            $table->unsignedInteger('livreur_id');
            $table->unsignedBigInteger('periode_id');

            $table->string('type');
            $table->bigInteger('montant');
            $table->string('motif');

            $table->string('statut')->default('En attente');
            $table->unsignedInteger('cree_par')->nullable();
            $table->unsignedInteger('valide_par')->nullable();
            $table->date('date_validation')->nullable();

            $table->unsignedBigInteger('commande_id')->nullable();
            $table->timestamps();

            $table->index('paie_livreur_id');
            $table->index('livreur_id');
            $table->index('periode_id');
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paie_ajustements');
    }
};
