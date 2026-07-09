<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('commandes')
            ->where('statut', 'Non Livré')
            ->where(function ($query) {
                $query->whereNotNull('date_livraison')
                    ->orWhereNotNull('date_retour');
            })
            ->update([
                'date_livraison' => null,
                'date_retour' => null,
            ]);
    }

    public function down(): void
    {
        // Données incohérentes : pas de restauration possible.
    }
};
