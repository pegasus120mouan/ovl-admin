<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('dette')) {
            Schema::create('dette', function (Blueprint $table) {
                $table->id();
                $table->string('type')->default('A payer');
                $table->boolean('remboursable')->default(true);
                $table->string('nom_debiteur');
                $table->unsignedBigInteger('montant_initial')->default(0);
                $table->unsignedBigInteger('montant_actuel')->default(0);
                $table->unsignedBigInteger('montants_payes')->default(0);
                $table->unsignedBigInteger('reste')->default(0);
                $table->date('date_dette');
                $table->date('date_echeance')->nullable();
                $table->string('statut')->nullable();
            });

            return;
        }

        Schema::table('dette', function (Blueprint $table) {
            if (!Schema::hasColumn('dette', 'type')) {
                $table->string('type')->default('A payer');
            }
            if (!Schema::hasColumn('dette', 'remboursable')) {
                $table->boolean('remboursable')->default(true);
            }
            if (!Schema::hasColumn('dette', 'nom_debiteur')) {
                $table->string('nom_debiteur');
            }
            if (!Schema::hasColumn('dette', 'montant_initial')) {
                $table->unsignedBigInteger('montant_initial')->default(0);
            }
            if (!Schema::hasColumn('dette', 'montant_actuel')) {
                $table->unsignedBigInteger('montant_actuel')->default(0);
            }
            if (!Schema::hasColumn('dette', 'montants_payes')) {
                $table->unsignedBigInteger('montants_payes')->default(0);
            }
            if (!Schema::hasColumn('dette', 'reste')) {
                $table->unsignedBigInteger('reste')->default(0);
            }
            if (!Schema::hasColumn('dette', 'date_dette')) {
                $table->date('date_dette')->nullable();
            }
            if (!Schema::hasColumn('dette', 'date_echeance')) {
                $table->date('date_echeance')->nullable();
            }
            if (!Schema::hasColumn('dette', 'statut')) {
                $table->string('statut')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dette');
    }
};
