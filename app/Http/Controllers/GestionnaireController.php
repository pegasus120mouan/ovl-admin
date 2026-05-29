<?php

namespace App\Http\Controllers;

use App\Models\Boutique;
use App\Models\Utilisateur;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GestionnaireController extends Controller
{
    public function index()
    {
        $gestionnaires = Utilisateur::gestionnaires()
            ->with('boutique')
            ->orderBy('nom')
            ->orderBy('prenoms')
            ->get();

        $gestionnairesTotal = $gestionnaires->count();
        $gestionnairesActifs = $gestionnaires->where('statut_compte', true)->count();
        $gestionnairesInactifs = $gestionnaires->where('statut_compte', false)->count();
        $boutiquesTotal = Boutique::count();

        return view('gestionnaires.index', compact(
            'gestionnaires',
            'gestionnairesTotal',
            'gestionnairesActifs',
            'gestionnairesInactifs',
            'boutiquesTotal'
        ));
    }

    public function create()
    {
        $boutiques = Boutique::orderBy('nom')->get();
        return view('gestionnaires.create', compact('boutiques'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenoms' => 'required|string|max:255',
            'contact' => 'required|string|max:15',
            'login' => 'required|string|max:255|unique:utilisateurs,login',
            'password' => 'required|string|min:4',
            'boutique_id' => 'required|exists:boutiques,id',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $pin = (string) random_int(100000, 999999);

        $data = [
            'nom' => $validated['nom'],
            'prenoms' => $validated['prenoms'],
            'contact' => $validated['contact'],
            'login' => $validated['login'],
            'password' => hash('sha256', $validated['password']),
            'code_pin' => $pin,
            'role' => 'gestionnaire',
            'boutique_id' => $validated['boutique_id'],
            'statut_compte' => true,
            'avatar' => 'default.jpg',
        ];

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('gestionnaires', 'r2');
        }

        $gestionnaire = Utilisateur::create($data);

        try {
            SmsService::sendPin($gestionnaire->contact, $pin, $gestionnaire->nom, $gestionnaire->prenoms);
        } catch (\Throwable $e) {
            // On ne bloque pas la création si l'envoi SMS échoue
        }

        return redirect()->route('gestionnaires.index')->with('success', 'Gestionnaire créé avec succès. Code PIN envoyé par SMS.');
    }

    public function show(Utilisateur $gestionnaire)
    {
        if ($gestionnaire->role !== 'gestionnaire') {
            abort(404);
        }

        $gestionnaire->load('boutique');

        $avatarKey = $gestionnaire->avatar ?: null;
        if (!$avatarKey || $avatarKey === 'default.jpg') {
            $avatarKey = 'gestionnaires/gestionnaire.png';
        } elseif (!str_contains($avatarKey, '/')) {
            $avatarKey = 'gestionnaires/' . $avatarKey;
        }

        $disk = Storage::disk('r2');
        try {
            $avatarUrl = $disk->temporaryUrl($avatarKey, now()->addMinutes(30));
        } catch (\Exception $e) {
            $avatarUrl = $disk->url($avatarKey);
        }

        $boutiques = Boutique::orderBy('nom')->get();

        return view('gestionnaires.show', compact('gestionnaire', 'avatarUrl', 'boutiques'));
    }

    public function update(Request $request, Utilisateur $gestionnaire)
    {
        if ($gestionnaire->role !== 'gestionnaire') {
            abort(404);
        }

        $validated = $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'prenoms' => 'sometimes|required|string|max:255',
            'contact' => 'sometimes|required|string|max:15',
            'login' => 'sometimes|required|string|max:255|unique:utilisateurs,login,' . $gestionnaire->id,
            'password' => 'nullable|string|min:4',
            'boutique_id' => 'sometimes|required|exists:boutiques,id',
            'statut_compte' => 'nullable|boolean',
            'avatar' => 'nullable|image|max:2048',
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
        if (array_key_exists('boutique_id', $validated)) {
            $data['boutique_id'] = $validated['boutique_id'];
        }
        if (array_key_exists('statut_compte', $validated)) {
            $data['statut_compte'] = $validated['statut_compte'];
        }

        if (!empty($validated['password'] ?? null)) {
            $data['password'] = hash('sha256', $validated['password']);
        }

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('gestionnaires', 'r2');
        }

        if (!empty($data)) {
            $gestionnaire->update($data);
        }

        $redirectTo = $request->input('redirect_to');
        if (is_string($redirectTo) && $redirectTo !== '') {
            return redirect()->to($redirectTo)->with('success', 'Gestionnaire modifié avec succès');
        }

        return redirect()->route('gestionnaires.index')->with('success', 'Gestionnaire modifié avec succès');
    }

    public function destroy(Utilisateur $gestionnaire)
    {
        if ($gestionnaire->role !== 'gestionnaire') {
            abort(404);
        }

        $gestionnaire->delete();

        return redirect()->route('gestionnaires.index')->with('success', 'Gestionnaire supprimé avec succès');
    }

    public function toggleStatus(Utilisateur $gestionnaire)
    {
        if ($gestionnaire->role !== 'gestionnaire') {
            abort(404);
        }

        $gestionnaire->update([
            'statut_compte' => !$gestionnaire->statut_compte
        ]);

        $status = $gestionnaire->statut_compte ? 'activé' : 'désactivé';
        return redirect()->back()->with('success', "Compte gestionnaire {$status} avec succès");
    }

    public function regeneratePin(Utilisateur $gestionnaire)
    {
        if ($gestionnaire->role !== 'gestionnaire') {
            abort(404);
        }

        $pin = (string) random_int(100000, 999999);
        $gestionnaire->update(['code_pin' => $pin]);

        try {
            SmsService::sendPin($gestionnaire->contact, $pin, $gestionnaire->nom, $gestionnaire->prenoms);
            return redirect()->back()->with('success', 'Nouveau code PIN généré et envoyé par SMS');
        } catch (\Throwable $e) {
            return redirect()->back()->with('warning', 'Nouveau code PIN généré mais l\'envoi SMS a échoué. PIN: ' . $pin);
        }
    }
}
