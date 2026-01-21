<?php

namespace App\Http\Controllers;

use App\Models\Boutique;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class BoutiqueController extends Controller
{
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $boutiques = Boutique::all();
            return response()->json($boutiques);
        }

        $perPage = $request->integer('per_page', 20);

        $boutiquesTotal = Boutique::count();
        $clientsTotal = Utilisateur::where('role', 'clients')->whereNotNull('boutique_id')->count();
        $boutiquesAvecLogo = Boutique::whereNotNull('logo')
            ->where('logo', '!=', '')
            ->where('logo', '!=', 'boutiques/default_boutiques.png')
            ->count();
        $boutiquesAvecTypeArticles = Boutique::whereNotNull('type_articles')->where('type_articles', '!=', '')->count();

        $boutiques = Boutique::query()
            ->with(['gerant'])
            ->withCount('utilisateurs')
            ->orderBy('nom')
            ->paginate($perPage)
            ->withQueryString();

        return view('boutiques.index', compact(
            'boutiques',
            'boutiquesTotal',
            'clientsTotal',
            'boutiquesAvecLogo',
            'boutiquesAvecTypeArticles'
        ));
    }

    public function store(Request $request)
    {
        if ($request->expectsJson()) {
            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'logo' => 'nullable|string|max:255',
                'type_articles' => 'nullable|string|max:255',
                'statut' => 'sometimes|boolean',
            ]);

            $boutique = Boutique::create($validated);
            return response()->json($boutique, 201);
        }

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'type_articles' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'statut' => 'sometimes|boolean',
        ]);

        $data = [
            'nom' => $validated['nom'],
            'type_articles' => $validated['type_articles'] ?? null,
            'statut' => array_key_exists('statut', $validated) ? (bool) $validated['statut'] : true,
        ];

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('boutiques', 's3');
        } else {
            $data['logo'] = 'boutiques/default_boutiques.png';
        }

        Boutique::create($data);

        return redirect()->route('boutiques.index')->with('success', 'Boutique ajoutée avec succès');
    }

    public function show(Boutique $boutique)
    {
        if (request()->expectsJson()) {
            return response()->json($boutique->load('utilisateurs'));
        }

        $boutique->load('gerant');

        $clientsTotal = Utilisateur::where('role', 'clients')->count();
        $clientsActifs = Utilisateur::where('role', 'clients')->where('statut_compte', 1)->count();
        $clientsInactifs = Utilisateur::where('role', 'clients')->where('statut_compte', 0)->count();
        $boutiquesTotal = Boutique::count();

        $clients = Utilisateur::query()
            ->where('role', 'clients')
            ->orderBy('nom')
            ->orderBy('prenoms')
            ->get();

        $logoKey = $boutique->logo ?: 'boutiques/default_boutiques.png';
        $disk = \Illuminate\Support\Facades\Storage::disk('s3');

        try {
            $logoUrl = $disk->temporaryUrl($logoKey, now()->addMinutes(30));
        } catch (\Exception $e) {
            $logoUrl = $disk->url($logoKey);
        }

        $clientsCount = $boutique->utilisateurs()->where('role', 'clients')->count();
        $commandesCount = $boutique->commandes()->count();

        return view('boutiques.profile', compact(
            'boutique',
            'logoUrl',
            'clientsCount',
            'commandesCount',
            'clients',
            'clientsTotal',
            'clientsActifs',
            'clientsInactifs',
            'boutiquesTotal'
        ));
    }

    public function update(Request $request, Boutique $boutique)
    {
        if ($request->expectsJson()) {
            $validated = $request->validate([
                'nom' => 'sometimes|required|string|max:255',
                'logo' => 'nullable|string|max:255',
                'type_articles' => 'nullable|string|max:255',
                'statut' => 'sometimes|boolean',
            ]);

            $boutique->update($validated);
            return response()->json($boutique);
        }

        $validated = $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'type_articles' => 'sometimes|nullable|string|max:255',
            'gerant_id' => 'sometimes|nullable|integer|exists:utilisateurs,id',
            'logo' => 'sometimes|image|mimes:jpg,jpeg,png,webp|max:2048',
            'statut' => 'sometimes|boolean',
        ]);

        $data = [];

        if (array_key_exists('nom', $validated)) {
            $data['nom'] = $validated['nom'];
        }

        if (array_key_exists('type_articles', $validated)) {
            $data['type_articles'] = $validated['type_articles'];
        }

        if (array_key_exists('statut', $validated)) {
            $data['statut'] = (bool) $validated['statut'];
        }

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('boutiques', 's3');
        }

        if (!empty($data)) {
            $boutique->update($data);
        }

        if (array_key_exists('gerant_id', $validated) && $validated['gerant_id']) {
            $nouveauGerant = Utilisateur::query()
                ->where('id', $validated['gerant_id'])
                ->where('role', 'clients')
                ->firstOrFail();

            $ancienGerant = $boutique->gerant()->first();
            if ($ancienGerant && $ancienGerant->id !== $nouveauGerant->id) {
                $ancienGerant->update(['boutique_id' => null]);
            }

            $nouveauGerant->update(['boutique_id' => $boutique->id]);
        }

        return redirect()
            ->route('boutiques.show', $boutique)
            ->with('success', 'Mise à jour effectuée avec succès');
    }

    public function destroy(Boutique $boutique)
    {
        $hasCommandes = $boutique->commandes()->exists();
        if ($hasCommandes) {
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'Impossible de supprimer cette boutique : des commandes y sont rattachées.',
                ], 422);
            }

            return redirect()
                ->route('boutiques.index')
                ->with('error', 'Impossible de supprimer cette boutique : des commandes y sont rattachées.');
        }

        DB::transaction(function () use ($boutique) {
            Utilisateur::where('boutique_id', $boutique->id)->update(['boutique_id' => null]);
            $boutique->delete();
        });

        if (request()->expectsJson()) {
            return response()->json(null, 204);
        }

        return redirect()
            ->route('boutiques.index')
            ->with('success', 'Boutique supprimée avec succès');
    }

    public function toggleStatut(Boutique $boutique)
    {
        $boutique->update(['statut' => !$boutique->statut]);

        if (request()->expectsJson()) {
            return response()->json([
                'id' => $boutique->id,
                'statut' => (bool) $boutique->statut,
            ]);
        }

        return redirect()
            ->route('boutiques.index')
            ->with('success', 'Statut de la boutique mis à jour.');
    }

    public function getUtilisateurs(Boutique $boutique)
    {
        return response()->json($boutique->utilisateurs);
    }

    public function getCommandes(Boutique $boutique)
    {
        return response()->json($boutique->commandes);
    }
}
