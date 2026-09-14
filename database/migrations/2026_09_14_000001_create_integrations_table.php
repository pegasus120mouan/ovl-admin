<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('integrations')) {
            return;
        }

        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->integer('utilisateur_id');
            $table->string('identifiant', 64)->unique();
            $table->string('token_hash');
            $table->string('token_hint', 12)->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->index('utilisateur_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integrations');
    }
};
