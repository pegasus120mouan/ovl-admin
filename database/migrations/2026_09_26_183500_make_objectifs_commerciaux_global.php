<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('objectifs_commerciaux') || ! Schema::hasColumn('objectifs_commerciaux', 'commercial_id')) {
            return;
        }

        $keep = DB::table('objectifs_commerciaux')
            ->selectRaw('MAX(id) as id')
            ->groupBy('periode')
            ->pluck('id');

        if ($keep->isNotEmpty()) {
            DB::table('objectifs_commerciaux')->whereNotIn('id', $keep)->delete();
        }

        Schema::table('objectifs_commerciaux', function (Blueprint $table) {
            $table->dropForeign(['commercial_id']);
            $table->dropUnique(['commercial_id', 'periode']);
            $table->dropColumn('commercial_id');
            $table->unique('periode');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('objectifs_commerciaux') || Schema::hasColumn('objectifs_commerciaux', 'commercial_id')) {
            return;
        }

        Schema::table('objectifs_commerciaux', function (Blueprint $table) {
            $table->dropUnique(['periode']);
            $table->integer('commercial_id')->nullable();
        });
    }
};
