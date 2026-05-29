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
        if (!Schema::hasColumn('commandes', 'gestionnaire_id')) {
            Schema::table('commandes', function (Blueprint $table) {
                $table->integer('gestionnaire_id')->nullable()->after('livreur_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->dropColumn('gestionnaire_id');
        });
    }
};
