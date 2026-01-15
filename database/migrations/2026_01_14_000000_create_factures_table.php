<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->unsignedInteger('client_id');
            $table->date('date_facture');
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->string('statut')->default('Brouillon');
            $table->unsignedBigInteger('total_ht')->default(0);
            $table->unsignedBigInteger('total_ttc')->default(0);
            $table->unsignedInteger('tva_taux')->nullable();
            $table->unsignedBigInteger('tva_montant')->nullable();
            $table->unsignedBigInteger('remise')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('client_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factures');
    }
};
