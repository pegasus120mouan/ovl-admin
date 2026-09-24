<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('commissions')->delete();

        Schema::table('commissions', function (Blueprint $table) {
            $table->dropForeign(['commande_id']);
            $table->dropForeign(['commercial_id']);
            $table->dropUnique(['commande_id']);
        });

        Schema::table('commissions', function (Blueprint $table) {
            $table->dropColumn(['commande_id', 'montant', 'statut', 'date_commission', 'commentaire']);
        });

        Schema::table('commissions', function (Blueprint $table) {
            $table->decimal('taux', 5, 2);
            $table->unique('commercial_id');
            $table->foreign('commercial_id')->references('id')->on('utilisateurs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropForeign(['commercial_id']);
            $table->dropUnique(['commercial_id']);
            $table->dropColumn('taux');
        });

        Schema::table('commissions', function (Blueprint $table) {
            $table->integer('commande_id');
            $table->integer('montant')->default(0);
            $table->string('statut')->default('Non payée');
            $table->date('date_commission')->nullable();
            $table->string('commentaire')->nullable();
            $table->foreign('commercial_id')->references('id')->on('utilisateurs');
            $table->foreign('commande_id')->references('id')->on('commandes');
            $table->unique('commande_id');
        });
    }
};
