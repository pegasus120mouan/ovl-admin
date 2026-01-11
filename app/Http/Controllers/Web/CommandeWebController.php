<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\Utilisateur;
use Illuminate\Http\Request;

class CommandeWebController extends Controller
{
    public function index(Request $request)
    {
        $query = Commande::with(['client.boutique', 'livreur']);

        // Filtres
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('date_reception', [$request->date_debut, $request->date_fin]);
        } elseif ($request->filled('date')) {
            $query->whereDate('date_reception', $request->date);
        }

        if ($request->filled('client_id')) {
            $query->where('utilisateur_id', $request->client_id);
        }

        if ($request->filled('livreur_id')) {
            $query->where('livreur_id', $request->livreur_id);
        }

        $commandes = $query->orderBy('date_reception', 'desc')->paginate(20);

        $clients = Utilisateur::clients()->get();
        $livreurs = Utilisateur::livreurs()->get();

        // Statistiques
        $stats = [
            'total' => Commande::count(),
            'livrees' => Commande::where('statut', 'Livré')->count(),
            'non_livrees' => Commande::where('statut', 'Non Livré')->count(),
            'retours' => Commande::where('statut', 'Retour')->count(),
            'cout_total' => Commande::sum('cout_reel'),
        ];

        return view('commandes.index', compact('commandes', 'clients', 'livreurs', 'stats'));
    }

    public function create()
    {
        $clients = Utilisateur::clients()->actifs()->get();
        $livreurs = Utilisateur::livreurs()->actifs()->get();

        return view('commandes.create', compact('clients', 'livreurs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'utilisateur_id' => 'required|exists:utilisateurs,id',
            'livreur_id' => 'required|exists:utilisateurs,id',
            'communes' => 'required|string|max:255',
            'cout_global' => 'required|integer|min:0',
            'cout_livraison' => 'required|integer|min:0',
            'date_reception' => 'required|date',
        ]);

        $validated['cout_reel'] = $validated['cout_global'] - $validated['cout_livraison'];
        $validated['statut'] = 'Non Livré';

        Commande::create($validated);

        return redirect()->route('commandes.index')->with('success', 'Commande créée avec succès!');
    }

    public function show(Commande $commande)
    {
        $commande->load(['client', 'livreur']);
        return view('commandes.show', compact('commande'));
    }

    public function edit(Commande $commande)
    {
        $clients = Utilisateur::clients()->get();
        $livreurs = Utilisateur::livreurs()->get();

        return view('commandes.edit', compact('commande', 'clients', 'livreurs'));
    }

    public function update(Request $request, Commande $commande)
    {
        $validated = $request->validate([
            'utilisateur_id' => 'required|exists:utilisateurs,id',
            'livreur_id' => 'required|exists:utilisateurs,id',
            'communes' => 'required|string|max:255',
            'cout_global' => 'required|integer|min:0',
            'cout_livraison' => 'required|integer|min:0',
            'statut' => 'required|in:Non Livré,Livré,Retour',
            'date_reception' => 'required|date',
            'date_livraison' => 'nullable|date',
            'date_retour' => 'nullable|date',
        ]);

        $validated['cout_reel'] = $validated['cout_global'] - $validated['cout_livraison'];

        // Mise à jour automatique des dates selon le statut
        if ($validated['statut'] === 'Livré' && !$commande->date_livraison) {
            $validated['date_livraison'] = now()->toDateString();
        }
        if ($validated['statut'] === 'Retour' && !$commande->date_retour) {
            $validated['date_retour'] = now()->toDateString();
        }
        if ($validated['statut'] === 'Non Livré') {
            $validated['date_livraison'] = null;
            $validated['date_retour'] = null;
        }

        if (array_key_exists('date_livraison', $validated) && $validated['date_livraison'] === null && $commande->date_livraison && $validated['statut'] !== 'Non Livré') {
            unset($validated['date_livraison']);
        }
        if (array_key_exists('date_retour', $validated) && $validated['date_retour'] === null && $commande->date_retour && $validated['statut'] !== 'Non Livré') {
            unset($validated['date_retour']);
        }

        $commande->update($validated);

        return redirect()->route('commandes.index')->with('success', 'Commande mise à jour avec succès!');
    }

    public function destroy(Commande $commande)
    {
        $commande->delete();
        return redirect()->route('commandes.index')->with('success', 'Commande supprimée avec succès!');
    }

    public function marquerLivre(Commande $commande)
    {
        $commande->update([
            'statut' => 'Livré',
            'date_livraison' => now()->toDateString(),
        ]);

        return redirect()->back()->with('success', 'Commande marquée comme livrée!');
    }

    public function marquerRetour(Commande $commande)
    {
        $commande->update([
            'statut' => 'Retour',
            'date_retour' => now()->toDateString(),
        ]);

        return redirect()->back()->with('success', 'Commande marquée comme retour!');
    }
}
