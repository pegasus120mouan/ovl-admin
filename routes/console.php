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

        $pointLivreur = PointsLivreur::query()
            ->where('utilisateur_id', $livreurId)
            ->whereDate('date_commande', $date)
            ->first();

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

Schedule::command('points-livreurs:sync-recettes')->hourly();
