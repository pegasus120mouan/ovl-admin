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
        if (Schema::hasColumn('utilisateurs', 'commercial_id')) {
            Schema::table('utilisateurs', function (Blueprint $table) {
                $table->dropColumn('commercial_id');
            });
        }

        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->integer('commercial_id')->nullable()->after('boutique_id');
            $table->foreign('commercial_id')->references('id')->on('utilisateurs');
        });

        Schema::table('boutiques', function (Blueprint $table) {
            $table->integer('commercial_id')->nullable()->after('commune_id');
            $table->foreign('commercial_id')->references('id')->on('utilisateurs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boutiques', function (Blueprint $table) {
            $table->dropForeign(['commercial_id']);
            $table->dropColumn('commercial_id');
        });

        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->dropForeign(['commercial_id']);
            $table->dropColumn('commercial_id');
        });
    }
};
