<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paie_periodes', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->string('statut')->default('Brouillon');
            $table->timestamps();

            $table->index('date_debut');
            $table->index('date_fin');
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paie_periodes');
    }
};
