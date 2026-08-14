<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('points_livreurs', function (Blueprint $table) {
            if (!Schema::hasColumn('points_livreurs', 'montant_verse')) {
                $table->unsignedBigInteger('montant_verse')->default(0)->after('depense');
            }
        });
    }

    public function down(): void
    {
        Schema::table('points_livreurs', function (Blueprint $table) {
            if (Schema::hasColumn('points_livreurs', 'montant_verse')) {
                $table->dropColumn('montant_verse');
            }
        });
    }
};
