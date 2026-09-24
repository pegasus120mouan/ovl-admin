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
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->integer('commercial_id');
            $table->integer('commande_id');
            $table->integer('montant')->default(0);
            $table->string('statut')->default('Non payée');
            $table->date('date_commission')->nullable();
            $table->string('commentaire')->nullable();

            $table->foreign('commercial_id')->references('id')->on('utilisateurs');
            $table->foreign('commande_id')->references('id')->on('commandes');
            $table->unique('commande_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
