<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\CoutLivraison;
use App\Models\Boutique;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class CommandeController extends Controller
{
    public function index()
    {
        $perPage = request('per_page', 20);
        
        $query = Commande::with(['client.boutique', 'livreur']);
        
        // Filtres de recherche
        if (request('communes')) {
            $query->where('communes', 'like', '%' . request('communes') . '%');
        }
        if (request('statut')) {
            $query->where('statut', request('statut'));
        }
        if (request('livreur_id')) {
            $query->where('livreur_id', request('livreur_id'));
        }
        if (request('boutique_id')) {
            $query->whereHas('client.boutique', function($q) {
                $q->where('id', request('boutique_id'));
            });
        }
        if (request('date_reception')) {
            $query->whereDate('date_reception', request('date_reception'));
        }
        if (request('date_livraison')) {
            $query->whereDate('date_livraison', request('date_livraison'));
        }
        if (request('date_retour')) {
            $query->whereDate('date_retour', request('date_retour'));
        }
        
        $commandes = $query->orderBy('date_reception', 'desc')->paginate($perPage)->withQueryString();
        $coutsLivraison = CoutLivraison::all();
        $boutiques = Boutique::all();
        $livreurs = Utilisateur::livreurs()->get();
        return view('commandes.index', compact('commandes', 'coutsLivraison', 'boutiques', 'livreurs'));
    }

    public function livrees()
    {
        $perPage = request('per_page', 20);
        $commandes = Commande::with(['client.boutique', 'livreur'])
            ->where('statut', 'Livré')
            ->orderBy('date_livraison', 'desc')
            ->paginate($perPage);
        $coutsLivraison = CoutLivraison::all();
        $boutiques = Boutique::all();
        $livreurs = Utilisateur::livreurs()->get();
        return view('commandes.livrees', compact('commandes', 'coutsLivraison', 'boutiques', 'livreurs'));
    }

    public function nonLivrees()
    {
        $perPage = request('per_page', 20);
        $commandes = Commande::with(['client.boutique', 'livreur'])
            ->where('statut', 'Non Livré')
            ->orderBy('date_reception', 'desc')
            ->paginate($perPage);
        $coutsLivraison = CoutLivraison::all();
        $boutiques = Boutique::all();
        $livreurs = Utilisateur::livreurs()->get();
        return view('commandes.non-livrees', compact('commandes', 'coutsLivraison', 'boutiques', 'livreurs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'utilisateur_id' => 'nullable|exists:utilisateurs,id',
            'livreur_id' => 'nullable|exists:utilisateurs,id',
            'communes' => 'required|string|max:255',
            'cout_global' => 'required|integer',
            'cout_livraison' => 'required|integer',
            'cout_reel' => 'required|integer',
            'statut' => 'nullable|string|max:255',
            'date_reception' => 'required|date',
            'date_livraison' => 'nullable|date',
            'date_retour' => 'nullable|date',
        ]);

        $commande = Commande::create($validated);
        return redirect()->route('commandes.index')->with('success', 'Commande enregistrée avec succès');
    }

    public function show(Commande $commande)
    {
        return response()->json($commande->load(['client', 'livreur']));
    }

    public function update(Request $request, Commande $commande)
    {
        $validated = $request->validate([
            'utilisateur_id' => 'nullable|exists:utilisateurs,id',
            'livreur_id' => 'nullable|exists:utilisateurs,id',
            'communes' => 'sometimes|required|string|max:255',
            'cout_global' => 'sometimes|required|integer',
            'cout_livraison' => 'sometimes|required|integer',
            'cout_reel' => 'sometimes|required|integer',
            'statut' => 'nullable|string|max:255',
            'date_reception' => 'sometimes|required|date',
            'date_livraison' => 'nullable|date',
            'date_retour' => 'nullable|date',
        ]);

        // Gérer les dates selon le statut
        if (isset($validated['statut'])) {
            if ($validated['statut'] === 'Livré') {
                $validated['date_livraison'] = now()->toDateString();
            } elseif ($validated['statut'] === 'Retour') {
                $validated['date_retour'] = now()->toDateString();
            }
        }

        $commande->update($validated);
        return redirect()->route('commandes.index')->with('success', 'Commande mise à jour avec succès');
    }

    public function destroy(Commande $commande)
    {
        $commande->delete();
        return response()->json(null, 204);
    }

    public function getLivrees()
    {
        $commandes = Commande::livre()->with(['client', 'livreur'])->get();
        return response()->json($commandes);
    }

    public function getNonLivrees()
    {
        $commandes = Commande::nonLivre()->with(['client', 'livreur'])->get();
        return response()->json($commandes);
    }

    public function getRetours()
    {
        $commandes = Commande::retour()->with(['client', 'livreur'])->get();
        return response()->json($commandes);
    }

    public function getByDate($date)
    {
        $commandes = Commande::parDate($date)->with(['client', 'livreur'])->get();
        return response()->json($commandes);
    }

    public function getByPeriode(Request $request)
    {
        $validated = $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date',
        ]);

        $commandes = Commande::parPeriode($validated['date_debut'], $validated['date_fin'])
            ->with(['client', 'livreur'])
            ->get();
        return response()->json($commandes);
    }

    public function getByClient($clientId)
    {
        $commandes = Commande::where('utilisateur_id', $clientId)
            ->with(['client', 'livreur'])
            ->get();
        return response()->json($commandes);
    }

    public function getByLivreur($livreurId)
    {
        $commandes = Commande::where('livreur_id', $livreurId)
            ->with(['client', 'livreur'])
            ->get();
        return response()->json($commandes);
    }

    public function marquerLivre(Commande $commande)
    {
        $commande->update([
            'statut' => 'Livré',
            'date_livraison' => now()->toDateString(),
        ]);
        return response()->json($commande);
    }

    public function marquerRetour(Commande $commande)
    {
        $commande->update([
            'statut' => 'Retour',
            'date_retour' => now()->toDateString(),
        ]);
        return response()->json($commande);
    }

    public function getStatistiques()
    {
        $stats = [
            'total' => Commande::count(),
            'livrees' => Commande::livre()->count(),
            'non_livrees' => Commande::nonLivre()->count(),
            'retours' => Commande::retour()->count(),
            'cout_total' => Commande::sum('cout_global'),
            'cout_livraison_total' => Commande::sum('cout_livraison'),
            'cout_reel_total' => Commande::sum('cout_reel'),
        ];
        return response()->json($stats);
    }
    
    public function print(Request $request)
    {
        $boutiqueId = $request->get('boutique_id');
        $date = $request->get('date');
        
        $boutique = Boutique::find($boutiqueId);
        
        // Récupérer les commandes du client pour la date sélectionnée (toutes les commandes)
        // Inclure les commandes livrées (date_livraison) ET les commandes non livrées (date_reception)
        $commandes = Commande::with(['client.boutique'])
            ->whereHas('client.boutique', function($q) use ($boutiqueId) {
                $q->where('id', $boutiqueId);
            })
            ->where(function($q) use ($date) {
                $q->whereDate('date_livraison', $date)
                  ->orWhere(function($q2) use ($date) {
                      $q2->whereDate('date_reception', $date)
                         ->where('statut', 'Non Livré');
                  });
            })
            ->get();
        
        // Total uniquement pour les commandes livrées
        $total = $commandes->where('statut', 'Livré')->sum('cout_global');
        
        // Générer le PDF
        $pdf = Pdf::loadView('commandes.print', compact('commandes', 'boutique', 'date', 'total'));
        
        // Nom du fichier
        $fileName = 'Point_du_' . \Carbon\Carbon::parse($date)->format('d-m-Y') . '_de_' . str_replace(' ', '_', $boutique->nom ?? 'Client') . '.pdf';

        return $pdf->stream($fileName);
    }
}
