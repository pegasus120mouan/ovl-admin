<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\CoutLivraison;
use App\Models\Boutique;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class CommandeController extends Controller
{
    public function index()
    {
        $perPage = request('per_page', 20);

        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();
        
        $query = Commande::with(['client.boutique', 'livreur']);
        
        // Filtres de recherche
        if (request('communes')) {
            $query->where('communes', 'like', '%' . request('communes') . '%');
        }
        if (request('statut')) {
            $query->where('statut', request('statut'));
        }
        if (request('statut') === 'Livré' && !request()->filled('date_livraison')) {
            $query->whereNotNull('date_livraison')->whereDate('date_livraison', Carbon::now()->toDateString());
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

        $statsMois = [
            'recus' => Commande::whereBetween('date_reception', [$startOfMonth, $endOfMonth])->count(),
            'livrees' => Commande::where('statut', 'Livré')->whereBetween('date_livraison', [$startOfMonth, $endOfMonth])->count(),
            'non_livrees' => Commande::where('statut', 'Non Livré')->whereBetween('date_reception', [$startOfMonth, $endOfMonth])->count(),
            'retours' => Commande::where('statut', 'Retour')->whereBetween('date_retour', [$startOfMonth, $endOfMonth])->count(),
        ];

        $coutsLivraison = CoutLivraison::all();
        $boutiques = Boutique::all();
        $livreurs = Utilisateur::livreurs()->get();
        return view('commandes.index', compact('commandes', 'coutsLivraison', 'boutiques', 'livreurs', 'statsMois'));
    }

    public function livrees()
    {
        $perPage = request('per_page', 20);

        $dateLivraison = request('date_livraison', Carbon::now()->toDateString());

        $commandes = Commande::with(['client.boutique', 'livreur'])
            ->where('statut', 'Livré')
            ->whereNotNull('date_livraison')
            ->whereDate('date_livraison', $dateLivraison)
            ->orderBy('date_livraison', 'desc')
            ->paginate($perPage)
            ->withQueryString();
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

    public function edit(Request $request, Commande $commande)
    {
        $clients = Utilisateur::clients()->get();
        $livreurs = Utilisateur::livreurs()->get();

        return view('commandes.edit', compact('commande', 'clients', 'livreurs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'utilisateur_id' => 'nullable|exists:utilisateurs,id',
            'livreur_id' => 'nullable|exists:utilisateurs,id',
            'communes' => 'required|string|max:255',
            'cout_global' => 'required|integer',
            'cout_livraison' => 'required|integer',
            'cout_reel' => 'sometimes|nullable|integer',
            'statut' => 'nullable|string|max:255',
            'date_reception' => 'required|date',
            'date_livraison' => 'nullable|date',
            'date_retour' => 'nullable|date',
            'redirect_to' => 'sometimes|nullable|string',
        ]);

        if (!array_key_exists('cout_reel', $validated) || $validated['cout_reel'] === null || (int) $validated['cout_reel'] === 0) {
            $validated['cout_reel'] = max(0, (int) $validated['cout_global'] - (int) $validated['cout_livraison']);
        }

        $commande = Commande::create($validated);

        $redirectTo = $request->input('redirect_to');
        if (is_string($redirectTo) && $redirectTo !== '') {
            return redirect()->to($redirectTo)->with('success', 'Commande enregistrée avec succès');
        }

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
            'cout_reel' => 'sometimes|nullable|integer',
            'statut' => 'nullable|string|max:255',
            'date_reception' => 'sometimes|required|date',
            'date_livraison' => 'nullable|date',
            'date_retour' => 'nullable|date',
            'redirect_to' => 'sometimes|nullable|string',
        ]);

        if (array_key_exists('cout_global', $validated) && array_key_exists('cout_livraison', $validated)) {
            if (!array_key_exists('cout_reel', $validated) || $validated['cout_reel'] === null || (int) $validated['cout_reel'] === 0) {
                $validated['cout_reel'] = max(0, (int) $validated['cout_global'] - (int) $validated['cout_livraison']);
            }
        }

        // Gérer les dates selon le statut
        if (isset($validated['statut'])) {
            if ($validated['statut'] === 'Livré') {
                $validated['date_livraison'] = now()->toDateString();
            } elseif ($validated['statut'] === 'Retour') {
                $validated['date_retour'] = now()->toDateString();
            }
        }

        $commande->update($validated);

        $redirectTo = $request->input('redirect_to');
        if (is_string($redirectTo) && $redirectTo !== '') {
            return redirect()->to($redirectTo)->with('success', 'Commande mise à jour avec succès');
        }

        return redirect()->route('commandes.index')->with('success', 'Commande mise à jour avec succès');
    }

    public function destroy(Commande $commande)
    {
        $commande->delete();

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        $redirectTo = request()->input('redirect_to');
        if (is_string($redirectTo) && $redirectTo !== '') {
            return redirect()->to($redirectTo)->with('success', 'Commande supprimée avec succès');
        }

        return redirect()->back()->with('success', 'Commande supprimée avec succès');
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
        $livreurId = $request->get('livreur_id');
        $date = $request->get('date');

        $boutique = null;
        $livreur = null;
        $total = 0;

        $commandesQuery = Commande::query()->with(['client.boutique', 'livreur']);

        if ($livreurId) {
            $livreur = Utilisateur::find($livreurId);
            $commandesQuery->where('livreur_id', $livreurId);
        } else {
            $boutique = Boutique::find($boutiqueId);
            $commandesQuery->whereHas('client.boutique', function($q) use ($boutiqueId) {
                $q->where('id', $boutiqueId);
            });
        }

        if ($livreurId) {
            $commandes = $commandesQuery
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
        } else {
            $commandes = $commandesQuery
                ->where(function($q) use ($date) {
                    $q->whereDate('date_livraison', $date)
                      ->where('statut', 'Livré')
                      ->orWhere(function($q2) use ($date) {
                          $q2->whereDate('date_reception', $date)
                             ->where('statut', 'Non Livré');
                      });
                })
                ->get();

            // Total uniquement pour les commandes livrées
            $total = $commandes->where('statut', 'Livré')->sum('cout_reel');
        }
        
        // Générer le PDF
        $view = $livreurId ? 'commandes.print_livreur' : 'commandes.print';
        $pdf = Pdf::loadView($view, compact('commandes', 'boutique', 'livreur', 'date', 'total'));
        
        // Nom du fichier
        $ownerName = $livreur ? trim(($livreur->nom ?? '') . ' ' . ($livreur->prenoms ?? '')) : ($boutique->nom ?? 'Client');
        $fileName = 'Point_du_' . \Carbon\Carbon::parse($date)->format('d-m-Y') . '_de_' . str_replace(' ', '_', $ownerName ?: 'Client') . '.pdf';

        return $pdf->stream($fileName);
    }
}
