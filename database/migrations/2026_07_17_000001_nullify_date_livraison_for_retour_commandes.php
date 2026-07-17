<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('commandes')
            ->where('statut', 'Retour')
            ->whereNotNull('date_livraison')
            ->update(['date_livraison' => null]);
    }

    public function down(): void
    {
        // Données incohérentes : pas de restauration possible.
    }
};
