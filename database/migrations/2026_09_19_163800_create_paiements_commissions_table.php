<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements_commissions', function (Blueprint $table) {
            $table->id();
            $table->integer('commercial_id');
            $table->date('periode');
            $table->integer('montant')->default(0);
            $table->date('date_paiement')->nullable();

            $table->foreign('commercial_id')->references('id')->on('utilisateurs');
            $table->unique(['commercial_id', 'periode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements_commissions');
    }
};
