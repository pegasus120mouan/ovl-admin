<?php

use App\Models\PointsLivreur;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $groups = DB::table('points_livreurs')
            ->select('utilisateur_id', DB::raw('DATE(date_commande) as jour'))
            ->groupBy('utilisateur_id', DB::raw('DATE(date_commande)'))
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($groups as $group) {
            PointsLivreur::consolidateDuplicatesForLivreurDay(
                (int) $group->utilisateur_id,
                $group->jour
            );
        }
    }

    public function down(): void
    {
        // Fusion non réversible.
    }
};
