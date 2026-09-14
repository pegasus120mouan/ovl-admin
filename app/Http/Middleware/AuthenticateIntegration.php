<?php

namespace App\Http\Middleware;

use App\Models\Integration;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateIntegration
{
    public function handle(Request $request, Closure $next): Response
    {
        $identifiant = $request->header('X-Integration-Id')
            ?: $request->header('X-Identifiant')
            ?: $request->input('identifiant');

        $token = $request->bearerToken()
            ?: $request->header('X-Integration-Token')
            ?: $request->input('token');

        if (!$identifiant || !$token) {
            return response()->json([
                'message' => 'Identifiant et token d\'intégration requis.',
            ], 401);
        }

        $integration = Integration::query()
            ->where('identifiant', $identifiant)
            ->where('actif', true)
            ->first();

        if (!$integration || !$integration->matchesToken($token)) {
            return response()->json([
                'message' => 'Identifiants d\'intégration invalides.',
            ], 401);
        }

        $request->attributes->set('integration', $integration);

        return $next($request);
    }
}
