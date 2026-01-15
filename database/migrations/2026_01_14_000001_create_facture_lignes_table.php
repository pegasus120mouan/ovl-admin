<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facture_lignes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('facture_id');
            $table->unsignedBigInteger('commande_id')->nullable();
            $table->unsignedInteger('quantite')->default(1);
            $table->text('designation');
            $table->unsignedBigInteger('prix_unitaire')->default(0);
            $table->unsignedBigInteger('prix_total')->default(0);
            $table->string('statut')->nullable();
            $table->timestamps();

            $table->foreign('facture_id')->references('id')->on('factures')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facture_lignes');
    }
};
