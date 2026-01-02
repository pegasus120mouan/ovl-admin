<?php

namespace App\Http\Controllers;

use App\Models\Boutique;
use App\Models\Commande;
use App\Models\CoutLivraison;
use App\Models\PointsLivreur;
use App\Models\Utilisateur;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PointsLivreurController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $date = $request->get('date');
        
        // Points livreurs avec pagination
        $query = PointsLivreur::with('livreur');
        $statsQuery = PointsLivreur::query();
        
        if ($date) {
            $query->whereDate('date_commande', $date);
            $statsQuery->whereDate('date_commande', $date);
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
