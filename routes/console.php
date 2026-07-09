<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Commande;
use App\Models\PointsLivreur;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('points-livreurs:sync-recettes {--date= : Date YYYY-MM-DD (par defaut aujourd\'hui)}', function () {
    $date = $this->option('date') ?: Carbon::today()->toDateString();

    $commandesLivrees = Commande::query()
        ->whereDate('date_livraison', $date)
        ->where('statut', 'Livré')
        ->get()
        ->groupBy('livreur_id');

    $updated = 0;
    foreach ($commandesLivrees as $livreurId => $commandes) {
        if (!$livreurId) {
            continue;
        }

        $recette = (int) $commandes->sum('cout_livraison');

        PointsLivreur::consolidateDuplicatesForLivreurDay((int) $livreurId, $date);

        $pointLivreur = PointsLivreur::forLivreurAndDate((int) $livreurId, $date);

        if ($pointLivreur) {
            $pointLivreur->recette = $recette;
            $pointLivreur->gain_jour = $recette - ((int) ($pointLivreur->depense ?? 0));
            $pointLivreur->save();
        } else {
            PointsLivreur::create([
                'utilisateur_id' => $livreurId,
                'recette' => $recette,
                'depense' => 0,
                'gain_jour' => $recette,
                'date_commande' => $date,
            ]);
        }

        $updated++;
    }

    $this->info("Synchro terminee pour {$date}. Livreurs traites: {$updated}");
})->purpose('Synchroniser les recettes PointsLivreur depuis les commandes livrees');

Artisan::command('points-livreurs:consolidate-duplicates', function () {
    $before = PointsLivreur::query()->count();

    PointsLivreur::consolidateAllDuplicates();

    $after = PointsLivreur::query()->count();
    $merged = $before - $after;

    $this->info("Consolidation terminee. Lignes fusionnees: {$merged}. Total actuel: {$after}.");
})->purpose('Fusionner les points livreurs en double (meme livreur, meme jour)');

Schedule::command('points-livreurs:sync-recettes')->hourly();
