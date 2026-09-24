<?php

use App\Models\Utilisateur;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->string('code_commercial', 20)->nullable()->unique()->after('login');
        });

        Utilisateur::query()
            ->commerciaux()
            ->whereNull('code_commercial')
            ->orderBy('id')
            ->each(function (Utilisateur $commercial) {
                $commercial->update([
                    'code_commercial' => $commercial->defaultCommercialCode(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->dropUnique(['code_commercial']);
            $table->dropColumn('code_commercial');
        });
    }
};
