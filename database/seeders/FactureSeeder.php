<?php

namespace Database\Seeders;

use App\Models\Commande;
use App\Models\Facture;
use App\Models\FactureLigne;
use App\Models\Utilisateur;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FactureSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('factures') || !Schema::hasTable('facture_lignes')) {
            return;
        }

        if (!Schema::hasTable('utilisateurs') || !Schema::hasTable('commandes')) {
            return;
        }

        $clients = Utilisateur::clients()->inRandomOrder()->take(5)->get();
        if ($clients->isEmpty()) {
            return;
        }

        foreach ($clients as $client) {
            $end = Carbon::today();
            $start = (clone $end)->subDays(14);

            $commandes = Commande::query()
                ->where('utilisateur_id', $client->id)
                ->where('statut', 'Livré')
                ->whereNotNull('date_livraison')
                ->whereDate('date_livraison', '>=', $start->toDateString())
                ->whereDate('date_livraison', '<=', $end->toDateString())
                ->orderBy('date_livraison')
                ->orderBy('id')
                ->get();

            if ($commandes->isEmpty()) {
                continue;
            }

            DB::transaction(function () use ($client, $start, $end, $commandes) {
                $dateFacture = Carbon::today();
                $year = $dateFacture->format('Y');
                $count = Facture::query()->whereYear('date_facture', $year)->count() + 1;
                $numero = 'INV/' . $year . '/' . str_pad((string) $count, 5, '0', STR_PAD_LEFT);

                $facture = Facture::create([
                    'numero' => $numero,
                    'client_id' => $client->id,
                    'date_facture' => $dateFacture->toDateString(),
                    'date_debut' => $start->toDateString(),
                    'date_fin' => $end->toDateString(),
                    'statut' => 'Brouillon',
                    'total_ht' => 0,
                    'total_ttc' => 0,
                ]);

                $total = 0;
                foreach ($commandes as $commande) {
                    $prixUnitaire = (int) ($commande->cout_global ?? 0);
                    $quantite = 1;
                    $prixTotal = $quantite * $prixUnitaire;

                    $designation = 'Prestation de livraison - Ref CMD' . $commande->id;
                    if (!empty($commande->communes)) {
                        $designation .= ' - ' . $commande->communes;
                    }
                    if (!empty($commande->date_livraison)) {
                        $designation .= ' - Livree le ' . Carbon::parse($commande->date_livraison)->format('d/m/Y');
                    }

                    FactureLigne::create([
                        'facture_id' => $facture->id,
                        'commande_id' => $commande->id,
                        'quantite' => $quantite,
                        'designation' => $designation,
                        'prix_unitaire' => $prixUnitaire,
                        'prix_total' => $prixTotal,
                    ]);

                    $total += $prixTotal;
                }

                $facture->total_ht = $total;
                $facture->total_ttc = $total;
                $facture->save();
            });
        }
    }
}
