<?php

namespace App\Providers;

use App\Models\Commande;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layout.main', function ($view) {
            $commandesRecuesAujourdHui = Commande::query()
                ->whereDate('date_reception', Carbon::today())
                ->count();

            // Nombre de points validés non payés (groupés par client et date)
            $pointsValidesNonPayes = Commande::where('point_valide', true)
                ->whereNotNull('date_validation_point')
                ->where(function($q) {
                    $q->where('paiement_effectue', false)
                      ->orWhereNull('paiement_effectue');
                })
                ->select('date_livraison', 'utilisateur_id', 'date_validation_point')
                ->groupBy('date_livraison', 'utilisateur_id', 'date_validation_point')
                ->get()
                ->count();

            // Nombre de réclamations en attente
            $reclamationsEnAttente = \App\Models\Reclamation::where('statut', 'en_attente')->count();

            $view->with('commandesRecuesAujourdHui', $commandesRecuesAujourdHui);
            $view->with('pointsValidesNonPayes', $pointsValidesNonPayes);
            $view->with('reclamationsEnAttente', $reclamationsEnAttente);
        });
    }
}
