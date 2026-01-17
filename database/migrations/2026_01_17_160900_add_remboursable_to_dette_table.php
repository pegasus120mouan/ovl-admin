<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('dette')) {
            return;
        }

        Schema::table('dette', function (Blueprint $table) {
            if (!Schema::hasColumn('dette', 'remboursable')) {
                $table->boolean('remboursable')->default(true)->after('type');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('dette')) {
            return;
        }

        Schema::table('dette', function (Blueprint $table) {
            if (Schema::hasColumn('dette', 'remboursable')) {
                $table->dropColumn('remboursable');
            }
        });
    }
};
