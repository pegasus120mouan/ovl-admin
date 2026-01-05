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

            $view->with('commandesRecuesAujourdHui', $commandesRecuesAujourdHui);
        });
    }
}
