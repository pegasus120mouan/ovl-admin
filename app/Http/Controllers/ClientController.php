<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use App\Models\Boutique;
use App\Services\SmsService;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $perPage = request('per_page', 20);
        
        $clients = Utilisateur::with('boutique')
            ->where('role', 'clients')
            ->paginate($perPage)
            ->withQueryString();
        
        $clientsActifs = Utilisateur::where('role', 'clients')->where('statut_compte', 1)->count();
        $clientsInactifs = Utilisateur::where('role', 'clients')->where('statut_compte', 0)->count();
        $boutiquesLibres = Boutique::query()
            ->whereDoesntHave('utilisateurs')
            ->orderBy('nom')
            ->get();

        $boutiques = Boutique::query()
            ->withCount('utilisateurs')
            ->orderBy('nom')
            ->get();
        
        return view('clients.index', compact('clients', 'boutiques', 'boutiquesLibres', 'clientsActifs', 'clientsInactifs'));
    }

    public function show(Request $request, Utilisateur $client)
    {
        if ($request->expectsJson()) {
            return response()->json($client->load('boutique'));
        }

        $client->load('boutique.gerant');

        $clientsTotal = Utilisateur::where('role', 'clients')->count();
        $clientsActifs = Utilisateur::where('role', 'clients')->where('statut_compte', 1)->count();
        $clientsInactifs = Utilisateur::where('role', 'clients')->where('statut_compte', 0)->count();
        $boutiquesTotal = Boutique::count();

        $avatarKey = $client->avatar ?: null;
        if (!$avatarKey || $avatarKey === 'default.jpg') {
            $avatarKey = 'utilisateurs/utilisateurs.png';
        } elseif (!str_contains($avatarKey, '/')) {
            $avatarKey = 'utilisateurs/' . $avatarKey;
        }

        $disk = \Illuminate\Support\Facades\Storage::disk('s3');
        try {
            $avatarUrl = $disk->temporaryUrl($avatarKey, now()->addMinutes(30));
        } catch (\Exception $e) {
            $avatarUrl = $disk->url($avatarKey);
        }

        $boutiques = Boutique::query()
            ->withCount('utilisateurs')
            ->orderBy('nom')
            ->get();

        return view('clients.profile', compact(
            'client',
            'avatarUrl',
            'boutiques',
            'clientsTotal',
            'clientsActifs',
            'clientsInactifs',
            'boutiquesTotal'
        ));
    }
    
    public function store(Request $request)
    {
        $pin = (string) random_int(100000, 999999);

        $client = Utilisateur::create([
            'nom' => $request->nom,
            'prenoms' => $request->prenoms,
            'contact' => $request->contact,
            'login' => $request->login,
            'password' => hash('sha256', $request->password),
            'code_pin' => $pin,
            'role' => 'clients',
            'boutique_id' => $request->boutique_id,
            'statut_compte' => $request->statut_compte,
            'avatar' => 'utilisateurs/utilisateurs.png',
        ]);

        try {
            SmsService::sendPin($client->contact, $pin, $client->nom, $client->prenoms);
        } catch (\Throwable $e) {
            // On ne bloque pas la création si l'envoi SMS échoue
        }
        
        return redirect()->route('clients.index')->with('success', 'Client ajouté avec succès');
    }
    
    public function update(Request $request, Utilisateur $client)
    {
        $validated = $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'prenoms' => 'sometimes|required|string|max:255',
            'contact' => 'sometimes|required|string|max:255',
            'boutique_id' => 'sometimes|nullable|integer|exists:boutiques,id',
            'statut_compte' => 'sometimes|nullable|boolean',
            'password' => 'sometimes|nullable|string|min:4',
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
            $data['avatar'] = $request->file('avatar')->store('utilisateurs', 's3');
        }

        if (!empty($data)) {
            $client->update($data);
        }

        $redirectTo = $request->input('redirect_to');
        if (is_string($redirectTo) && $redirectTo !== '') {
            return redirect()->to($redirectTo)->with('success', 'Client modifié avec succès');
        }

        return redirect()->route('clients.index')->with('success', 'Client modifié avec succès');
    }

    public function destroy(Utilisateur $client)
    {
        if ($client->role !== 'clients') {
            abort(404);
        }

        $client->delete();

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client supprimé avec succès');
    }

    public function resendSms(Utilisateur $client)
    {
        if ($client->role !== 'clients') {
            abort(404);
        }

        $pin = (string) random_int(100000, 999999);
        $client->update(['code_pin' => $pin]);

        try {
            SmsService::sendPin($client->contact, $pin, $client->nom, $client->prenoms);
        } catch (\Throwable $e) {
            return redirect()
                ->route('clients.index')
                ->with('error', "Echec d'envoi du SMS");
        }

        return redirect()
            ->route('clients.index')
            ->with('success', 'SMS renvoyé avec succès');
    }
}
