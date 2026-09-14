<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class IntegrationController extends Controller
{
    public function index()
    {
        if (!Session::has('utilisateur')) {
            return redirect()->route('login');
        }

        $integrations = Integration::query()
            ->with('client.boutique')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $clients = Utilisateur::query()
            ->with('boutique')
            ->where('role', 'clients')
            ->where('statut_compte', 1)
            ->orderBy('nom')
            ->orderBy('prenoms')
            ->get();

        $apiUrl = url('/api/v1/integrations/commandes');

        return view('integrations.index', compact('integrations', 'clients', 'apiUrl'));
    }

    public function store(Request $request)
    {
        if (!Session::has('utilisateur')) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'utilisateur_id' => 'required|exists:utilisateurs,id',
        ]);

        $client = Utilisateur::query()
            ->where('id', $validated['utilisateur_id'])
            ->where('role', 'clients')
            ->first();

        if (!$client) {
            return redirect()
                ->route('integrations.index')
                ->withInput()
                ->with('error', 'Le client sélectionné est invalide.');
        }

        $plainToken = Integration::generateToken();

        $integration = new Integration([
            'nom' => $validated['nom'],
            'utilisateur_id' => $client->id,
            'identifiant' => Integration::generateIdentifiant(),
            'actif' => true,
        ]);
        $integration->setPlainToken($plainToken);
        $integration->save();

        return redirect()
            ->route('integrations.index')
            ->with('success', 'Intégration créée avec succès. Copiez le token maintenant, il ne sera plus affiché en clair.')
            ->with('generated_credentials', [
                'id' => $integration->id,
                'identifiant' => $integration->identifiant,
                'token' => $plainToken,
                'url' => $integration->apiUrl(),
            ]);
    }

    public function update(Request $request, Integration $integration)
    {
        if (!Session::has('utilisateur')) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'utilisateur_id' => 'required|exists:utilisateurs,id',
            'actif' => 'nullable|boolean',
        ]);

        $client = Utilisateur::query()
            ->where('id', $validated['utilisateur_id'])
            ->where('role', 'clients')
            ->first();

        if (!$client) {
            return redirect()
                ->route('integrations.index')
                ->with('error', 'Le client sélectionné est invalide.');
        }

        $integration->update([
            'nom' => $validated['nom'],
            'utilisateur_id' => $client->id,
            'actif' => $request->boolean('actif'),
        ]);

        return redirect()
            ->route('integrations.index')
            ->with('success', 'Intégration mise à jour.');
    }

    public function regenerateToken(Integration $integration)
    {
        if (!Session::has('utilisateur')) {
            return redirect()->route('login');
        }

        $plainToken = Integration::generateToken();
        $integration->setPlainToken($plainToken);
        $integration->save();

        return redirect()
            ->route('integrations.index')
            ->with('success', 'Nouveau token généré. Copiez-le maintenant, il ne sera plus affiché en clair.')
            ->with('generated_credentials', [
                'id' => $integration->id,
                'identifiant' => $integration->identifiant,
                'token' => $plainToken,
                'url' => $integration->apiUrl(),
            ]);
    }

    public function destroy(Integration $integration)
    {
        if (!Session::has('utilisateur')) {
            return redirect()->route('login');
        }

        $integration->delete();

        return redirect()
            ->route('integrations.index')
            ->with('success', 'Intégration supprimée.');
    }
}
