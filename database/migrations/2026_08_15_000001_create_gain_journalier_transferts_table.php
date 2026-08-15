<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('gain_journalier_transferts')) {
            return;
        }

        Schema::create('gain_journalier_transferts', function (Blueprint $table) {
            $table->id();
            $table->date('date_gain')->unique();
            $table->unsignedBigInteger('montant_recette')->default(0);
            $table->unsignedBigInteger('montant_depense')->default(0);
            $table->bigInteger('montant_gain')->default(0);
            $table->timestamp('transfere_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gain_journalier_transferts');
    }
};
