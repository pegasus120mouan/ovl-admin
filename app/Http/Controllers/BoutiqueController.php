<?php

namespace App\Http\Controllers;

use App\Models\Boutique;
use App\Models\Commune;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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
            ->with(['gerant', 'commune'])
            ->withCount('utilisateurs')
            ->orderBy('nom')
            ->paginate($perPage)
            ->withQueryString();

        $communes = Commune::query()->orderBy('nom_commune')->get();

        return view('boutiques.index', compact(
            'boutiques',
            'boutiquesTotal',
            'clientsTotal',
            'boutiquesAvecLogo',
            'boutiquesAvecTypeArticles',
            'communes'
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
                'latitude' => 'sometimes|nullable|numeric|between:-90,90',
                'longitude' => 'sometimes|nullable|numeric|between:-180,180',
                'commune_id' => 'sometimes|nullable|integer|exists:communes,commune_id',
            ]);

            $boutique = Boutique::create($validated);

            return response()->json($boutique, 201);
        }

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'type_articles' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'statut' => 'sometimes|boolean',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'commune_id' => 'required|integer|exists:communes,commune_id',
        ]);

        $data = [
            'nom' => $validated['nom'],
            'type_articles' => $validated['type_articles'] ?? null,
            'statut' => array_key_exists('statut', $validated) ? (bool) $validated['statut'] : true,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'commune_id' => $validated['commune_id'],
        ];

        if ($request->hasFile('logo')) {
            $data['logo'] = $this->storeBoutiqueLogo($request->file('logo'));
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
        $disk = Storage::disk('r2');

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
                'latitude' => 'sometimes|nullable|numeric|between:-90,90',
                'longitude' => 'sometimes|nullable|numeric|between:-180,180',
                'commune_id' => 'sometimes|nullable|integer|exists:communes,commune_id',
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
            'latitude' => 'sometimes|nullable|numeric|between:-90,90',
            'longitude' => 'sometimes|nullable|numeric|between:-180,180',
            'commune_id' => 'sometimes|required|integer|exists:communes,commune_id',
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

        if (array_key_exists('latitude', $validated)) {
            $data['latitude'] = $validated['latitude'];
        }

        if (array_key_exists('longitude', $validated)) {
            $data['longitude'] = $validated['longitude'];
        }

        if (array_key_exists('commune_id', $validated)) {
            $data['commune_id'] = $validated['commune_id'];
        }

        if ($request->hasFile('logo')) {
            $ancienLogo = $boutique->logo;
            $data['logo'] = $this->storeBoutiqueLogo($request->file('logo'));
            $this->deleteBoutiqueLogo($ancienLogo);
        }

        if (! empty($data)) {
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
        $boutique->update(['statut' => ! $boutique->statut]);

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

    private function storeBoutiqueLogo(UploadedFile $logo): string
    {
        try {
            $path = $logo->store('boutiques', 'r2');
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'logo' => "Impossible d'envoyer le logo vers Cloudflare (ovl-delivery/boutiques). Vérifiez les clés API R2.",
            ]);
        }

        if (! is_string($path) || ! str_starts_with($path, 'boutiques/')) {
            throw ValidationException::withMessages([
                'logo' => "L'enregistrement du logo vers Cloudflare R2 a échoué.",
            ]);
        }

        return $path;
    }

    private function deleteBoutiqueLogo(?string $logoKey): void
    {
        if (! $logoKey || $logoKey === 'boutiques/default_boutiques.png') {
            return;
        }

        try {
            Storage::disk('r2')->delete($logoKey);
        } catch (\Throwable) {
            //
        }
    }
}
