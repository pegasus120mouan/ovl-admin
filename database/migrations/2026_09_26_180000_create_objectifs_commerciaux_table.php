<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('objectifs_commerciaux', function (Blueprint $table) {
            $table->id();
            $table->date('periode');
            $table->integer('montant')->default(0);

            $table->unique('periode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('objectifs_commerciaux');
    }
};
