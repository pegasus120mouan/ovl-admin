<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\Integration;
use Illuminate\Http\Request;

class IntegrationCommandeController extends Controller
{
    public function store(Request $request)
    {
        /** @var Integration $integration */
        $integration = $request->attributes->get('integration');

        $validated = $request->validate([
            'communes' => 'required|string|max:255',
            'cout_global' => 'required|integer|min:0',
            'cout_livraison' => 'required|integer|min:0',
            'cout_reel' => 'sometimes|nullable|integer',
            'date_reception' => 'nullable|date',
            'reference_externe' => 'nullable|string|max:100',
        ]);

        $coutGlobal = (int) $validated['cout_global'];
        $coutLivraison = (int) $validated['cout_livraison'];
        $coutReel = array_key_exists('cout_reel', $validated) && $validated['cout_reel'] !== null
            ? (int) $validated['cout_reel']
            : $coutGlobal - $coutLivraison;

        $commande = Commande::create([
            'utilisateur_id' => $integration->utilisateur_id,
            'communes' => $validated['communes'],
            'cout_global' => $coutGlobal,
            'cout_livraison' => $coutLivraison,
            'cout_reel' => $coutReel,
            'statut' => 'Non Livré',
            'date_reception' => $validated['date_reception'] ?? now()->toDateString(),
            'date_livraison' => null,
            'date_retour' => null,
        ]);

        return response()->json([
            'message' => 'Commande reçue pour livraison.',
            'commande' => [
                'id' => $commande->id,
                'utilisateur_id' => $commande->utilisateur_id,
                'communes' => $commande->communes,
                'cout_global' => (int) $commande->cout_global,
                'cout_livraison' => (int) $commande->cout_livraison,
                'cout_reel' => (int) $commande->cout_reel,
                'statut' => $commande->statut,
                'date_reception' => $commande->date_reception?->format('Y-m-d'),
                'reference_externe' => $validated['reference_externe'] ?? null,
                'integration' => $integration->nom,
            ],
        ], 201);
    }
}
