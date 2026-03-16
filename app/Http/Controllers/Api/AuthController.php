<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'contact' => 'required|string|max:15',
            'code_pin' => 'required|string|max:6',
        ]);

        $contact = preg_replace('/\s+/', '', $validated['contact']);
        
        // Normaliser le numéro (enlever le préfixe 225 si présent)
        if (str_starts_with($contact, '225') && strlen($contact) === 13) {
            $contact = substr($contact, 3);
        }

        $utilisateur = Utilisateur::query()
            ->where('role', 'clients')
            ->where(function ($query) use ($contact, $validated) {
                $query->where('contact', $contact)
                      ->orWhere('contact', '225' . $contact)
                      ->orWhere('contact', $validated['contact']);
            })
            ->first();

        if (!$utilisateur) {
            return response()->json([
                'success' => false,
                'message' => 'Numéro de téléphone non trouvé',
            ], 404);
        }

        if ($utilisateur->statut_compte != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Votre compte est désactivé. Contactez le support.',
            ], 403);
        }

        if ($utilisateur->code_pin !== $validated['code_pin']) {
            return response()->json([
                'success' => false,
                'message' => 'Code PIN incorrect',
            ], 401);
        }

        // Charger la boutique
        $utilisateur->load('boutique');

        // Générer un token simple
        $token = Str::random(64);
        $utilisateur->update(['api_token' => hash('sha256', $token)]);

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie',
            'data' => [
                'user' => [
                    'id' => $utilisateur->id,
                    'nom' => $utilisateur->nom,
                    'prenoms' => $utilisateur->prenoms,
                    'contact' => $utilisateur->contact,
                    'avatar' => $utilisateur->avatar,
                ],
                'boutique' => $utilisateur->boutique ? [
                    'id' => $utilisateur->boutique->id,
                    'nom' => $utilisateur->boutique->nom,
                ] : null,
                'token' => $token,
            ],
        ]);
    }

    public function me(Request $request)
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token manquant',
            ], 401);
        }

        $user = Utilisateur::where('api_token', hash('sha256', $token))->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalide',
            ], 401);
        }

        $user->load('boutique');

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'nom' => $user->nom,
                    'prenoms' => $user->prenoms,
                    'contact' => $user->contact,
                    'avatar' => $user->avatar,
                ],
                'boutique' => $user->boutique ? [
                    'id' => $user->boutique->id,
                    'nom' => $user->boutique->nom,
                ] : null,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->bearerToken();
        
        if ($token) {
            Utilisateur::where('api_token', hash('sha256', $token))
                ->update(['api_token' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie',
        ]);
    }

    public function resendPin(Request $request)
    {
        $validated = $request->validate([
            'contact' => 'required|string|max:15',
        ]);

        $contact = preg_replace('/\s+/', '', $validated['contact']);

        $utilisateur = Utilisateur::query()
            ->where('role', 'clients')
            ->where(function ($query) use ($contact, $validated) {
                $query->where('contact', $contact)
                      ->orWhere('contact', '225' . $contact)
                      ->orWhere('contact', $validated['contact']);
            })
            ->first();

        if (!$utilisateur) {
            return response()->json([
                'success' => false,
                'message' => 'Numéro de téléphone non trouvé',
            ], 404);
        }

        $pin = (string) random_int(100000, 999999);
        $utilisateur->update(['code_pin' => $pin]);

        try {
            SmsService::sendPin($utilisateur->contact, $pin, $utilisateur->nom, $utilisateur->prenoms);
        } catch (\Throwable $e) {
            Log::error('Resend PIN SMS failed', [
                'contact' => $utilisateur->contact,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => "Échec d'envoi du SMS. Réessayez plus tard.",
            ], 502);
        }

        return response()->json([
            'success' => true,
            'message' => 'Un nouveau code PIN a été envoyé par SMS',
        ]);
    }
}
