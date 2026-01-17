<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('versements')) {
            Schema::create('versements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('dette_id');
                $table->unsignedBigInteger('montant_versement');
                $table->date('date_versement');

                $table->index('dette_id');
                $table->foreign('dette_id')->references('id')->on('dette')->onDelete('cascade');
            });

            return;
        }

        Schema::table('versements', function (Blueprint $table) {
            if (!Schema::hasColumn('versements', 'dette_id')) {
                $table->unsignedBigInteger('dette_id');
            }
            if (!Schema::hasColumn('versements', 'montant_versement')) {
                $table->unsignedBigInteger('montant_versement');
            }
            if (!Schema::hasColumn('versements', 'date_versement')) {
                $table->date('date_versement');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('versements');
    }
};
