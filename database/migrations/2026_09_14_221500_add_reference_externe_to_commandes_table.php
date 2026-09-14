<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            if (!Schema::hasColumn('commandes', 'reference_externe')) {
                $table->string('reference_externe', 100)->nullable()->after('statut');
            }
            if (!Schema::hasColumn('commandes', 'integration_id')) {
                $table->unsignedBigInteger('integration_id')->nullable()->after('reference_externe');
            }
        });

        Schema::table('commandes', function (Blueprint $table) {
            $table->unique(['utilisateur_id', 'reference_externe'], 'commandes_client_ref_externe_unique');
            $table->index('integration_id');
        });
    }

    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->dropUnique('commandes_client_ref_externe_unique');
            $table->dropIndex(['integration_id']);
            $table->dropColumn(['reference_externe', 'integration_id']);
        });
    }
};
