<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $taux = DB::table('commissions')->value('taux');

        Schema::table('commissions', function (Blueprint $table) {
            $table->dropForeign(['commercial_id']);
            $table->dropUnique(['commercial_id']);
            $table->dropColumn('commercial_id');
        });

        DB::table('commissions')->delete();

        if ($taux !== null) {
            DB::table('commissions')->insert(['taux' => $taux]);
        }
    }

    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->integer('commercial_id')->nullable();
            $table->unique('commercial_id');
            $table->foreign('commercial_id')->references('id')->on('utilisateurs');
        });
    }
};
