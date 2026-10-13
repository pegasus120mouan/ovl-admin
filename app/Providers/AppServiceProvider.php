<?php

namespace App\Providers;

use App\Models\Commande;
use App\Models\PaiementLivreur;
use App\Models\Utilisateur;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
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
        Paginator::useBootstrapFour();

        Route::bind('commercial', function (string $value) {
            return Utilisateur::query()
                ->where('role', 'commercial')
                ->where(function ($query) use ($value) {
                    $query->where('id', $value)
                        ->orWhere('code_commercial', $value);
                })
                ->firstOrFail();
        });

        View::composer('layout.main', function ($view) {
            $commandesRecuesAujourdHui = Commande::query()
                ->whereDate('date_reception', Carbon::today())
                ->count();

            // Nombre de points validés non payés (groupés par client et date)
            $pointsValidesNonPayes = Commande::where('point_valide', true)
                ->whereNotNull('date_validation_point')
                ->where(function ($q) {
                    $q->where('paiement_effectue', false)
                        ->orWhereNull('paiement_effectue');
                })
                ->select('date_livraison', 'utilisateur_id', 'date_validation_point')
                ->groupBy('date_livraison', 'utilisateur_id', 'date_validation_point')
                ->get()
                ->count();

            // Nombre de réclamations en attente
            $reclamationsEnAttente = \App\Models\Reclamation::where('statut', 'en_attente')->count();

            // Nombre de paiements déclarés par les livreurs en attente de validation
            // (0 si la migration n'a pas encore été exécutée).
            $paiementsLivreursEnAttente = 0;
            try {
                if (Schema::hasTable('paiements_livreurs')) {
                    $paiementsLivreursEnAttente = PaiementLivreur::query()
                        ->where('statut', PaiementLivreur::STATUT_EN_ATTENTE)
                        ->count();
                }
            } catch (\Throwable $e) {
                $paiementsLivreursEnAttente = 0;
            }

            $view->with('paiementsLivreursEnAttente', $paiementsLivreursEnAttente);
            $view->with('commandesRecuesAujourdHui', $commandesRecuesAujourdHui);
            $view->with('pointsValidesNonPayes', $pointsValidesNonPayes);
            $view->with('reclamationsEnAttente', $reclamationsEnAttente);
        });
    }
}
