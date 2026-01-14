<?php

namespace App\Http\Controllers;

use App\Models\Boutique;
use App\Models\Commande;
use App\Models\CoutLivraison;
use App\Models\PointsLivreur;
use App\Models\Utilisateur;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PointsLivreurController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $date = $request->get('date');
        $dateDebut = $request->get('date_debut');
        $dateFin = $request->get('date_fin');
        $utilisateurId = $request->get('utilisateur_id');
        
        // Points livreurs avec pagination
        $query = PointsLivreur::with('livreur');
        $statsQuery = PointsLivreur::query();
        
        if ($date) {
            $query->whereDate('date_commande', $date);
            $statsQuery->whereDate('date_commande', $date);
        }

        if ($dateDebut) {
            $query->whereDate('date_commande', '>=', $dateDebut);
            $statsQuery->whereDate('date_commande', '>=', $dateDebut);
        }

        if ($dateFin) {
            $query->whereDate('date_commande', '<=', $dateFin);
            $statsQuery->whereDate('date_commande', '<=', $dateFin);
        }

        if ($utilisateurId) {
            $query->where('utilisateur_id', $utilisateurId);
            $statsQuery->where('utilisateur_id', $utilisateurId);
        }
        
        $pointsLivreurs = $query->orderBy('date_commande', 'desc')
            ->paginate($perPage)
            ->withQueryString();
        
        // Données pour les formulaires
        $livreurs = Utilisateur::livreurs()->get();
        
        // Statistiques
        $totalRecette = (clone $statsQuery)->sum('recette');
        $totalDepense = (clone $statsQuery)->sum('depense');
        $totalGain = (clone $statsQuery)->sum('gain_jour');
        $nombreLivreurs = (clone $statsQuery)->distinct('utilisateur_id')->count('utilisateur_id');
        
        return view('points_livreurs.index', compact(
            'pointsLivreurs',
            'livreurs',
            'date',
            'totalRecette',
            'totalDepense',
            'totalGain',
            'nombreLivreurs'
        ));
    }

    public function listeMontants(Request $request)
    {
        $dateDebut = $request->get('date_debut');
        $dateFin = $request->get('date_fin');
        $livreurId = $request->get('livreur_id');
        $perPage = $request->get('per_page', 50);

        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $statsBaseQuery = Commande::query();
        if ($livreurId) {
            $statsBaseQuery->where('livreur_id', $livreurId);
        }

        $statsMois = [
            'recus' => (clone $statsBaseQuery)->whereBetween('date_reception', [$startOfMonth, $endOfMonth])->count(),
            'livrees' => (clone $statsBaseQuery)->where('statut', 'Livré')->whereBetween('date_livraison', [$startOfMonth, $endOfMonth])->count(),
            'non_livrees' => (clone $statsBaseQuery)->where('statut', 'Non Livré')->whereBetween('date_reception', [$startOfMonth, $endOfMonth])->count(),
            'retours' => (clone $statsBaseQuery)->where('statut', 'Retour')->whereBetween('date_retour', [$startOfMonth, $endOfMonth])->count(),
        ];

        if ($dateDebut && $dateFin && Carbon::parse($dateFin)->lt(Carbon::parse($dateDebut))) {
            return redirect()->back()->with('error', 'La date de fin doit être supérieure ou égale à la date de début.');
        }

        $query = Commande::query()
            ->join('utilisateurs as livreurs', 'livreurs.id', '=', 'commandes.livreur_id')
            ->where('commandes.statut', 'Livré')
            ->whereNotNull('commandes.date_livraison')
            ->selectRaw("commandes.livreur_id, livreurs.nom, livreurs.prenoms, DATE(commandes.date_livraison) as jour, SUM(commandes.cout_livraison) as montant")
            ->groupBy('commandes.livreur_id', 'livreurs.nom', 'livreurs.prenoms', 'jour');

        if ($dateDebut) {
            $query->whereDate('commandes.date_livraison', '>=', $dateDebut);
        }

        if ($dateFin) {
            $query->whereDate('commandes.date_livraison', '<=', $dateFin);
        }

        if ($livreurId) {
            $query->where('commandes.livreur_id', $livreurId);
        }

        $rows = $query
            ->orderByDesc('jour')
            ->orderBy('livreurs.nom')
            ->paginate($perPage)
            ->withQueryString();

        $livreurs = Utilisateur::livreurs()->get();

        return view('points_livreurs.liste_montants', compact(
            'rows',
            'livreurs',
            'dateDebut',
            'dateFin',
            'livreurId',
            'statsMois'
        ));
    }

    public function printDepot(Request $request)
    {
        $utilisateurId = $request->get('utilisateur_id');
        $dateDebut = $request->get('date_debut');
        $dateFin = $request->get('date_fin');

        $request->validate([
            'utilisateur_id' => 'required|integer',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date',
        ]);

        if (Carbon::parse($dateFin)->lt(Carbon::parse($dateDebut))) {
            return redirect()->back()->with('error', 'La date de fin doit être supérieure ou égale à la date de début.');
        }

        $livreur = Utilisateur::findOrFail($utilisateurId);

        $commandesParJour = Commande::query()
            ->where('livreur_id', $utilisateurId)
            ->where('statut', 'Livré')
            ->whereDate('date_livraison', '>=', $dateDebut)
            ->whereDate('date_livraison', '<=', $dateFin)
            ->selectRaw('DATE(date_livraison) as jour, SUM(cout_global) as montant_global')
            ->groupBy('jour')
            ->pluck('montant_global', 'jour');

        $depensesParJour = PointsLivreur::query()
            ->where('utilisateur_id', $utilisateurId)
            ->whereDate('date_commande', '>=', $dateDebut)
            ->whereDate('date_commande', '<=', $dateFin)
            ->selectRaw('DATE(date_commande) as jour, SUM(depense) as depense')
            ->groupBy('jour')
            ->pluck('depense', 'jour');

        $rows = [];
        $period = \Carbon\CarbonPeriod::create(Carbon::parse($dateDebut), Carbon::parse($dateFin));
        foreach ($period as $day) {
            $jour = $day->format('Y-m-d');
            $montantGlobal = (int) ($commandesParJour[$jour] ?? 0);
            $depense = (int) ($depensesParJour[$jour] ?? 0);
            $montantADeposer = $montantGlobal - $depense;

            if ($montantGlobal === 0 && $depense === 0) {
                continue;
            }

            $rows[] = [
                'date' => $jour,
                'montant_global' => $montantGlobal,
                'depense' => $depense,
                'montant_a_deposer' => $montantADeposer,
            ];
        }

        $totalDepot = collect($rows)->sum('montant_a_deposer');

        $pdf = Pdf::loadView('points_livreurs.print_depot', [
            'livreur' => $livreur,
            'rows' => $rows,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'totalDepot' => $totalDepot,
        ]);

        $fileName = 'Point_versements_' . Carbon::parse($dateDebut)->format('d-m-Y') . '_au_' . Carbon::parse($dateFin)->format('d-m-Y') . '_' . str_replace(' ', '_', trim(($livreur->nom ?? '') . ' ' . ($livreur->prenoms ?? ''))) . '.pdf';

        return $pdf->stream($fileName);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'utilisateur_id' => 'required|integer',
            'recette' => 'required|integer',
            'depense' => 'nullable|integer',
            'date_commande' => 'required|date',
        ]);
        
        // Calculer le gain
        $validated['gain_jour'] = ($validated['recette'] ?? 0) - ($validated['depense'] ?? 0);

        PointsLivreur::create($validated);
        return redirect()->route('points-livreurs.index')->with('success', 'Point enregistre avec succes');
    }

    public function syncRecettes(Request $request)
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        
        // Récupérer les commandes livrées du jour groupées par livreur
        $commandesLivrees = Commande::with('livreur')
            ->whereDate('date_livraison', $date)
            ->where('statut', 'Livré')
            ->get()
            ->groupBy('livreur_id');
        
        foreach ($commandesLivrees as $livreurId => $commandes) {
            if (!$livreurId) continue;
            
            $recette = $commandes->sum('cout_livraison');
            
            // Chercher ou créer le point livreur pour cette date
            $pointLivreur = PointsLivreur::where('utilisateur_id', $livreurId)
                ->whereDate('date_commande', $date)
                ->first();
            
            if ($pointLivreur) {
                // Mettre à jour la recette et recalculer le gain
                $pointLivreur->recette = $recette;
                $pointLivreur->gain_jour = $recette - ($pointLivreur->depense ?? 0);
                $pointLivreur->save();
            } else {
                // Créer un nouveau point livreur
                PointsLivreur::create([
                    'utilisateur_id' => $livreurId,
                    'recette' => $recette,
                    'depense' => 0,
                    'gain_jour' => $recette,
                    'date_commande' => $date,
                ]);
            }
        }
        
        return redirect()->back()->with('success', 'Recettes synchronisees avec succes');
    }

    public function show(PointsLivreur $pointsLivreur)
    {
        return response()->json($pointsLivreur);
    }

    public function update(Request $request, PointsLivreur $pointsLivreur)
    {
        $validated = $request->validate([
            'utilisateur_id' => 'required|integer',
            'recette' => 'required|integer',
            'depense' => 'nullable|integer',
            'date_commande' => 'required|date',
        ]);
        
        $validated['gain_jour'] = ($validated['recette'] ?? 0) - ($validated['depense'] ?? 0);

        $pointsLivreur->update($validated);
        return redirect()->route('points-livreurs.index')->with('success', 'Point modifie avec succes');
    }

    public function destroy(PointsLivreur $pointsLivreur)
    {
        $pointsLivreur->delete();
        return redirect()->route('points-livreurs.index')->with('success', 'Point supprime avec succes');
    }

    public function getByLivreur($livreurId)
    {
        $points = PointsLivreur::where('livreur_id', $livreurId)
            ->orderBy('date_points', 'desc')
            ->get();
        return response()->json($points);
    }

    public function getTotalByLivreur($livreurId)
    {
        $total = PointsLivreur::where('livreur_id', $livreurId)->sum('points');
        return response()->json(['livreur_id' => $livreurId, 'total_points' => $total]);
    }

    public function getClassement()
    {
        $classement = PointsLivreur::selectRaw('livreur_id, SUM(points) as total_points')
            ->groupBy('livreur_id')
            ->orderByDesc('total_points')
            ->get();
        return response()->json($classement);
    }
}
