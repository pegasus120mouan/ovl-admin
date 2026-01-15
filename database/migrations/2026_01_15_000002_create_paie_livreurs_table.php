<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paie_livreurs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('periode_id');
            $table->unsignedInteger('livreur_id');

            $table->unsignedBigInteger('salaire_base')->default(0);
            $table->bigInteger('total_ajustements')->default(0);
            $table->bigInteger('net_a_payer')->default(0);

            $table->string('statut')->default('Brouillon');

            $table->date('date_validation')->nullable();
            $table->unsignedInteger('valide_par')->nullable();

            $table->date('date_paiement')->nullable();
            $table->unsignedBigInteger('montant_paye')->nullable();
            $table->string('reference_paiement')->nullable();
            $table->unsignedInteger('paye_par')->nullable();

            $table->timestamps();

            $table->unique(['periode_id', 'livreur_id']);
            $table->index('livreur_id');
            $table->index('periode_id');
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paie_livreurs');
    }
};
