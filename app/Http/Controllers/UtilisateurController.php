<?php

namespace App\Http\Controllers;

use App\Models\Boutique;
use App\Models\Commande;
use App\Models\CoutLivraison;
use App\Models\Utilisateur;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class UtilisateurController extends Controller
{
    public function index()
    {
        $utilisateurs = Utilisateur::with('boutique')->get();
        return response()->json($utilisateurs);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenoms' => 'required|string|max:255',
            'contact' => 'required|string|max:15',
            'login' => 'required|string|max:255|unique:utilisateurs',
            'avatar' => 'nullable|string|max:255',
            'password' => 'required|string|min:6',
            'role' => 'required|string|max:255',
            'boutique_id' => 'nullable|exists:boutiques,id',
            'statut_compte' => 'nullable|boolean',
        ]);

        $validated['password'] = hash('sha256', $validated['password']);

        if (($validated['role'] ?? null) === 'clients') {
            $pin = (string) random_int(100000, 999999);
            $validated['code_pin'] = $pin;
        }
        
        $utilisateur = Utilisateur::create($validated);

        if (($validated['role'] ?? null) === 'clients') {
            try {
                SmsService::sendPin($utilisateur->contact, $validated['code_pin'], $utilisateur->nom, $utilisateur->prenoms);
            } catch (\Throwable $e) {
                // On ne bloque pas la création si l'envoi SMS échoue
            }
        }

        return response()->json($utilisateur, 201);
    }

    public function show(Utilisateur $utilisateur)
    {
        return response()->json($utilisateur->load('boutique'));
    }

    public function update(Request $request, Utilisateur $utilisateur)
    {
        $validated = $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'prenoms' => 'sometimes|required|string|max:255',
            'contact' => 'sometimes|required|string|max:15',
            'login' => 'sometimes|required|string|max:255|unique:utilisateurs,login,' . $utilisateur->id,
            'avatar' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6',
            'role' => 'sometimes|required|string|max:255',
            'boutique_id' => 'nullable|exists:boutiques,id',
            'statut_compte' => 'nullable|boolean',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = hash('sha256', $validated['password']);
        }

        $utilisateur->update($validated);
        return response()->json($utilisateur);
    }

    public function destroy(Utilisateur $utilisateur)
    {
        $utilisateur->delete();
        return response()->json(null, 204);
    }

    public function getClients()
    {
        $clients = Utilisateur::clients()->with('boutique')->get();
        return response()->json($clients);
    }

    public function getLivreurs()
    {
        $livreurs = Utilisateur::livreurs()->get();
        return response()->json($livreurs);
    }

    public function getAdmins()
    {
        $admins = Utilisateur::admins()->get();
        return response()->json($admins);
    }

    public function getActifs()
    {
        $actifs = Utilisateur::actifs()->get();
        return response()->json($actifs);
    }

    public function toggleStatut(Utilisateur $utilisateur)
    {
        $utilisateur->statut_compte = !$utilisateur->statut_compte;
        $utilisateur->save();
        return response()->json($utilisateur);
    }

    public function verifyPin(Request $request)
    {
        $validated = $request->validate([
            'contact' => 'required|string|max:15',
            'code_pin' => 'required|string|size:6',
        ]);

        $utilisateur = Utilisateur::query()
            ->where('role', 'clients')
            ->where('contact', $validated['contact'])
            ->first();

        if (!$utilisateur) {
            return response()->json(['message' => 'Client introuvable'], 404);
        }

        if (($utilisateur->code_pin ?? null) !== $validated['code_pin']) {
            return response()->json(['message' => 'Code PIN incorrect'], 422);
        }

        $utilisateur->update([
            'statut_compte' => 1,
            'code_pin' => null,
        ]);

        return response()->json(['verified' => true]);
    }

    public function resendPin(Request $request)
    {
        $validated = $request->validate([
            'contact' => 'required|string|max:15',
        ]);

        $utilisateur = Utilisateur::query()
            ->where('role', 'clients')
            ->where('contact', $validated['contact'])
            ->first();

        if (!$utilisateur) {
            return response()->json(['message' => 'Client introuvable'], 404);
        }

        $pin = (string) random_int(100000, 999999);
        $utilisateur->update(['code_pin' => $pin]);

        try {
            SmsService::sendPin($utilisateur->contact, $pin, $utilisateur->nom, $utilisateur->prenoms);
        } catch (\Throwable $e) {
            return response()->json(['message' => "Echec d'envoi du SMS"], 502);
        }

        return response()->json(['sent' => true]);
    }

    public function getCommandesClient(Utilisateur $utilisateur)
    {
        return response()->json($utilisateur->commandesClient);
    }

    public function getCommandesLivreur(Utilisateur $utilisateur)
    {
        return response()->json($utilisateur->commandesLivreur);
    }

    public function administrateurs(Request $request)
    {
        $perPage = $request->integer('per_page', 20);

        $administrateurs = Utilisateur::query()
            ->where('role', 'admin')
            ->orderBy('nom')
            ->orderBy('prenoms')
            ->paginate($perPage)
            ->withQueryString();

        $administrateursActifs = Utilisateur::query()->where('role', 'admin')->where('statut_compte', 1)->count();
        $administrateursInactifs = Utilisateur::query()->where('role', 'admin')->where('statut_compte', 0)->count();
        $boutiquesTotal = Boutique::count();

        return view('users.administrateurs', compact('administrateurs', 'administrateursActifs', 'administrateursInactifs', 'boutiquesTotal'));
    }

    public function livreurs(Request $request)
    {
        $perPage = $request->integer('per_page', 20);

        $livreurs = Utilisateur::query()
            ->where('role', 'livreur')
            ->orderBy('nom')
            ->orderBy('prenoms')
            ->paginate($perPage)
            ->withQueryString();

        $livreursActifs = Utilisateur::query()->where('role', 'livreur')->where('statut_compte', 1)->count();
        $livreursInactifs = Utilisateur::query()->where('role', 'livreur')->where('statut_compte', 0)->count();
        $boutiquesTotal = Boutique::count();

        return view('users.livreurs', compact('livreurs', 'livreursActifs', 'livreursInactifs', 'boutiquesTotal'));
    }

    public function storeAdministrateurWeb(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenoms' => 'required|string|max:255',
            'contact' => 'required|string|max:15',
            'login' => 'required|string|max:255|unique:utilisateurs,login',
            'password' => 'required|string|min:4',
            'statut_compte' => 'nullable|boolean',
        ]);

        Utilisateur::create([
            'nom' => $validated['nom'],
            'prenoms' => $validated['prenoms'],
            'contact' => $validated['contact'],
            'login' => $validated['login'],
            'password' => hash('sha256', $validated['password']),
            'role' => 'admin',
            'statut_compte' => $validated['statut_compte'] ?? 1,
            'avatar' => 'administrateurs/admins.png',
        ]);

        return redirect()->route('users.administrateurs')->with('success', 'Administrateur ajouté avec succès');
    }

    public function storeLivreurWeb(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenoms' => 'required|string|max:255',
            'contact' => 'required|string|max:15',
            'login' => 'required|string|max:255|unique:utilisateurs,login',
            'password' => 'required|string|min:4',
            'statut_compte' => 'nullable|boolean',
        ]);

        $pin = null;
        for ($i = 0; $i < 10; $i++) {
            $candidate = (string) random_int(100000, 999999);

            $exists = Utilisateur::query()
                ->where('role', 'livreur')
                ->where('code_pin', $candidate)
                ->exists();

            if (!$exists) {
                $pin = $candidate;
                break;
            }
        }

        if (!$pin) {
            $pin = (string) random_int(100000, 999999);
        }

        $livreur = Utilisateur::create([
            'nom' => $validated['nom'],
            'prenoms' => $validated['prenoms'],
            'contact' => $validated['contact'],
            'login' => $validated['login'],
            'password' => hash('sha256', $validated['password']),
            'role' => 'livreur',
            'statut_compte' => $validated['statut_compte'] ?? 1,
            'avatar' => 'livreur.png',
            'code_pin' => $pin,
        ]);

        try {
            SmsService::sendPin($livreur->contact, $pin, $livreur->nom, $livreur->prenoms);
        } catch (\Throwable $e) {
            // On ne bloque pas la création si l'envoi SMS échoue
        }

        return redirect()->route('users.livreurs')->with('success', 'Livreur ajouté avec succès');
    }

    public function showAdministrateurWeb(Request $request, Utilisateur $admin)
    {
        if (($admin->role ?? null) !== 'admin') {
            abort(404);
        }

        $administrateursTotal = Utilisateur::query()->where('role', 'admin')->count();
        $administrateursActifs = Utilisateur::query()->where('role', 'admin')->where('statut_compte', 1)->count();
        $administrateursInactifs = Utilisateur::query()->where('role', 'admin')->where('statut_compte', 0)->count();
        $boutiquesTotal = Boutique::count();

        $avatarKey = $admin->avatar ?: null;
        if (!$avatarKey || $avatarKey === 'default.jpg') {
            $avatarKey = 'administrateurs/admins.png';
        } elseif (!str_contains($avatarKey, '/')) {
            $avatarKey = 'administrateurs/' . $avatarKey;
        }

        $disk = Storage::disk('s3');
        try {
            $avatarUrl = $disk->temporaryUrl($avatarKey, now()->addMinutes(30));
        } catch (\Exception $e) {
            $avatarUrl = $disk->url($avatarKey);
        }

        return view('users.administrateurs_profile', compact(
            'admin',
            'avatarUrl',
            'administrateursTotal',
            'administrateursActifs',
            'administrateursInactifs',
            'boutiquesTotal'
        ));
    }

    public function updateAdministrateurWeb(Request $request, Utilisateur $admin)
    {
        if (($admin->role ?? null) !== 'admin') {
            abort(404);
        }

        $validated = $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'prenoms' => 'sometimes|required|string|max:255',
            'contact' => 'sometimes|required|string|max:15',
            'login' => 'sometimes|required|string|max:255|unique:utilisateurs,login,' . $admin->id,
            'password' => 'sometimes|nullable|string|min:4|confirmed',
            'statut_compte' => 'sometimes|nullable|boolean',
            'avatar' => 'sometimes|image|mimes:jpg,jpeg,png,webp|max:2048',
            'redirect_to' => 'sometimes|nullable|string',
        ]);

        $data = [];

        if (array_key_exists('nom', $validated)) {
            $data['nom'] = $validated['nom'];
        }
        if (array_key_exists('prenoms', $validated)) {
            $data['prenoms'] = $validated['prenoms'];
        }
        if (array_key_exists('contact', $validated)) {
            $data['contact'] = $validated['contact'];
        }
        if (array_key_exists('login', $validated)) {
            $data['login'] = $validated['login'];
        }
        if (array_key_exists('statut_compte', $validated)) {
            $data['statut_compte'] = $validated['statut_compte'];
        }

        if (!empty($validated['password'] ?? null)) {
            $data['password'] = hash('sha256', $validated['password']);
        }

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('administrateurs', 's3');
        }

        if (!empty($data)) {
            $admin->update($data);
        }

        $sessionUser = Session::get('utilisateur');
        if (is_array($sessionUser) && (($sessionUser['id'] ?? null) == $admin->id)) {
            $sessionUser['nom'] = $admin->nom;
            $sessionUser['prenoms'] = $admin->prenoms;
            $sessionUser['login'] = $admin->login;
            $sessionUser['avatar'] = $admin->avatar;
            $sessionUser['role'] = $admin->role;
            Session::put('utilisateur', $sessionUser);
        }

        $redirectTo = $request->input('redirect_to');
        if (is_string($redirectTo) && $redirectTo !== '') {
            return redirect()->to($redirectTo)->with('success', 'Administrateur modifié avec succès');
        }

        return redirect()->route('users.administrateurs')->with('success', 'Administrateur modifié avec succès');
    }

    public function destroyAdministrateurWeb(Utilisateur $admin)
    {
        if (($admin->role ?? null) !== 'admin') {
            abort(404);
        }

        $admin->delete();

        return redirect()->route('users.administrateurs')->with('success', 'Administrateur supprimé avec succès');
    }

    public function toggleAdministrateurStatutWeb(Utilisateur $admin)
    {
        if (($admin->role ?? null) !== 'admin') {
            abort(404);
        }

        $admin->statut_compte = !$admin->statut_compte;
        $admin->save();

        return redirect()->route('users.administrateurs');
    }

    public function showLivreurWeb(Request $request, Utilisateur $livreur)
    {
        if (($livreur->role ?? null) !== 'livreur') {
            abort(404);
        }

        $livreursTotal = Utilisateur::query()->where('role', 'livreur')->count();
        $livreursActifs = Utilisateur::query()->where('role', 'livreur')->where('statut_compte', 1)->count();
        $livreursInactifs = Utilisateur::query()->where('role', 'livreur')->where('statut_compte', 0)->count();
        $boutiquesTotal = Boutique::count();

        $avatarKey = $livreur->avatar ?: null;
        if (!$avatarKey || $avatarKey === 'default.jpg') {
            $avatarKey = 'livreurs/livreur.png';
        } elseif (!str_contains($avatarKey, '/')) {
            $avatarKey = 'livreurs/' . $avatarKey;
        }

        $disk = Storage::disk('s3');
        try {
            $avatarUrl = $disk->temporaryUrl($avatarKey, now()->addMinutes(30));
        } catch (\Exception $e) {
            $avatarUrl = $disk->url($avatarKey);
        }

        return view('users.livreurs_profile', compact(
            'livreur',
            'avatarUrl',
            'livreursTotal',
            'livreursActifs',
            'livreursInactifs',
            'boutiquesTotal'
        ));
    }

    public function updateLivreurWeb(Request $request, Utilisateur $livreur)
    {
        if (($livreur->role ?? null) !== 'livreur') {
            abort(404);
        }

        $validated = $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'prenoms' => 'sometimes|required|string|max:255',
            'contact' => 'sometimes|required|string|max:15',
            'login' => 'sometimes|required|string|max:255|unique:utilisateurs,login,' . $livreur->id,
            'password' => 'sometimes|nullable|string|min:4|confirmed',
            'statut_compte' => 'sometimes|nullable|boolean',
            'avatar' => 'sometimes|image|mimes:jpg,jpeg,png,webp|max:2048',
            'redirect_to' => 'sometimes|nullable|string',
        ]);

        $data = [];

        if (array_key_exists('nom', $validated)) {
            $data['nom'] = $validated['nom'];
        }
        if (array_key_exists('prenoms', $validated)) {
            $data['prenoms'] = $validated['prenoms'];
        }
        if (array_key_exists('contact', $validated)) {
            $data['contact'] = $validated['contact'];
        }
        if (array_key_exists('login', $validated)) {
            $data['login'] = $validated['login'];
        }
        if (array_key_exists('statut_compte', $validated)) {
            $data['statut_compte'] = $validated['statut_compte'];
        }

        if (!empty($validated['password'] ?? null)) {
            $data['password'] = hash('sha256', $validated['password']);
        }

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('livreurs', 's3');
        }

        if (!empty($data)) {
            $livreur->update($data);
        }

        $redirectTo = $request->input('redirect_to');
        if (is_string($redirectTo) && $redirectTo !== '') {
            return redirect()->to($redirectTo)->with('success', 'Livreur modifié avec succès');
        }

        return redirect()->route('users.livreurs')->with('success', 'Livreur modifié avec succès');
    }

    public function commandesLivreurWeb(Request $request, Utilisateur $livreur)
    {
        if (($livreur->role ?? null) !== 'livreur') {
            abort(404);
        }

        $perPage = $request->integer('per_page', 20);

        $now = now();
        $monthLabel = $now->format('F');

        $kpiQuery = Commande::query()
            ->where('livreur_id', $livreur->id)
            ->whereYear('date_reception', $now->year)
            ->whereMonth('date_reception', $now->month);

        $montantGlobal = (int) $kpiQuery->clone()->sum('cout_global');
        $montantClients = (int) $kpiQuery->clone()->sum('cout_reel');
        $gain = (int) $kpiQuery->clone()->sum('cout_livraison');
        $nbreColisLivres = (int) $kpiQuery->clone()->where('statut', 'Livré')->count();

        $livreurs = Utilisateur::query()
            ->where('role', 'livreur')
            ->orderBy('nom')
            ->orderBy('prenoms')
            ->get();

        $coutsLivraison = CoutLivraison::all();
        $boutiques = Boutique::with('utilisateurs')->get();

        $commandes = Commande::query()
            ->with(['client.boutique', 'livreur'])
            ->where('livreur_id', $livreur->id)
            ->orderByDesc('date_reception')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('users.livreurs_commandes', compact(
            'livreur',
            'commandes',
            'livreurs',
            'coutsLivraison',
            'boutiques',
            'monthLabel',
            'montantGlobal',
            'montantClients',
            'gain',
            'nbreColisLivres'
        ));
    }

    public function destroyLivreurWeb(Utilisateur $livreur)
    {
        if (($livreur->role ?? null) !== 'livreur') {
            abort(404);
        }

        $livreur->delete();

        return redirect()->route('users.livreurs')->with('success', 'Livreur supprimé avec succès');
    }

    public function toggleLivreurStatutWeb(Utilisateur $livreur)
    {
        if (($livreur->role ?? null) !== 'livreur') {
            abort(404);
        }

        $livreur->statut_compte = !$livreur->statut_compte;
        $livreur->save();

        return redirect()->route('users.livreurs');
    }
}
