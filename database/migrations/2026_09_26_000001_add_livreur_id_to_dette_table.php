<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dette', function (Blueprint $table) {
            if (!Schema::hasColumn('dette', 'livreur_id')) {
                $table->unsignedBigInteger('livreur_id')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('dette', function (Blueprint $table) {
            if (Schema::hasColumn('dette', 'livreur_id')) {
                $table->dropIndex(['livreur_id']);
                $table->dropColumn('livreur_id');
            }
        });
    }
};
