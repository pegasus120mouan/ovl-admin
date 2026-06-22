<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public static function sendPin(string $to, string $pin, ?string $nom = null, ?string $prenoms = null): void
    {
        $baseUrl = (string) env('SMS_API_BASE_URL', 'https://www.hsms.ci');
        $baseUrl = preg_replace('#^https?://hsms\.ci#i', 'https://www.hsms.ci', $baseUrl) ?? $baseUrl;
        $url = rtrim($baseUrl, '/') . '/api/envoi-sms/';

        $token = trim((string) env('SMS_API_TOKEN', ''));
        $clientId = trim((string) env('SMS_CLIENT_ID', ''));
        $clientSecret = trim((string) env('SMS_CLIENT_SECRET', ''));

        if ($token === '' || $clientId === '' || $clientSecret === '') {
            Log::warning('HSMS sendPin skipped: missing SMS credentials');
            throw new \RuntimeException('SMS credentials missing');
        }

        $telephone = preg_replace('/\s+/', '', $to);
        if (is_string($telephone)) {
            $telephone = ltrim($telephone, '+');
        }

        if (is_string($telephone) && str_starts_with($telephone, '0') && strlen($telephone) === 10) {
            $telephone = '225' . $telephone;
        }

        $telephoneLocal = (string) $telephone;
        if (str_starts_with($telephoneLocal, '225')) {
            $telephoneLocal = substr($telephoneLocal, 3);
        }

        $template = (string) env(
            'SMS_PIN_MESSAGE_TEMPLATE',
            'Bienvenue sur OVLDELIVERY, {nom} {prenoms}. Vos identifiants de connexion sont : {telephone_local} / {pin}. Merci.'
        );

        $replacements = [
            '{pin}' => $pin,
            '{telephone}' => (string) $telephone,
            '{telephone_local}' => (string) $telephoneLocal,
            '{nom}' => (string) ($nom ?? ''),
            '{prenoms}' => (string) ($prenoms ?? ''),
        ];

        $message = strtr($template, $replacements);

        $multipart = [
            ['name' => 'clientid', 'contents' => $clientId],
            ['name' => 'clientsecret', 'contents' => $clientSecret],
            ['name' => 'telephone', 'contents' => (string) $telephone],
            ['name' => 'message', 'contents' => $message],
        ];

        try {
            $verifySsl = filter_var(env('SMS_VERIFY_SSL', true), FILTER_VALIDATE_BOOLEAN);

            $authPrefix = (string) env('SMS_AUTH_PREFIX', 'Bearer');
            $authHeader = trim($authPrefix . ' ' . trim($token));

            Log::info('HSMS sendPin auth header prepared', [
                'url' => $url,
                'auth_prefix' => $authPrefix,
                'has_token' => $token !== '',
                'has_client_id' => $clientId !== '',
                'has_client_secret' => $clientSecret !== '',
                'token_len' => strlen($token),
                'token_prefix' => substr($token, 0, 6),
                'token_suffix' => substr($token, -6),
                'client_id_len' => strlen($clientId),
                'client_secret_len' => strlen($clientSecret),
            ]);

            $request = Http::timeout((int) env('SMS_TIMEOUT', 10))
                ->withHeaders(['Authorization' => $authHeader])
                ->asMultipart();

            if (!$verifySsl) {
                $request = $request->withoutVerifying();
            }

            $response = $request->post($url, $multipart);

            if (in_array($response->status(), [301, 302, 307, 308], true)) {
                $redirectUrl = (string) $response->header('Location');

                if ($redirectUrl !== '') {
                    $redirectRequest = Http::timeout((int) env('SMS_TIMEOUT', 10))
                        ->withHeaders(['Authorization' => $authHeader])
                        ->asMultipart();

                    if (!$verifySsl) {
                        $redirectRequest = $redirectRequest->withoutVerifying();
                    }

                    $response = $redirectRequest->post($redirectUrl, $multipart);
                }
            }

            if ($response->status() === 401) {
                $fallbackPrefix = strtolower($authPrefix) === 'bearer' ? 'Token' : 'Bearer';
                $fallbackHeader = trim($fallbackPrefix . ' ' . trim($token));

                Log::warning('HSMS sendPin got 401, retrying with fallback auth prefix', [
                    'auth_prefix' => $authPrefix,
                    'fallback_prefix' => $fallbackPrefix,
                ]);

                $fallbackRequest = Http::timeout((int) env('SMS_TIMEOUT', 10))
                    ->withHeaders(['Authorization' => $fallbackHeader])
                    ->asMultipart();

                if (!$verifySsl) {
                    $fallbackRequest = $fallbackRequest->withoutVerifying();
                }

                $response = $fallbackRequest->post($url, $multipart);
            }

            if (!$response->successful()) {
                Log::error('HSMS sendPin failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'telephone' => (string) $telephone,
                ]);
            }

            $response->throw();
        } catch (\Throwable $e) {
            Log::error('HSMS sendPin exception', [
                'message' => $e->getMessage(),
                'telephone' => (string) $telephone,
            ]);
            throw $e;
        }
    }

    public static function sendMessage(string $to, string $message): void
    {
        $baseUrl = (string) env('SMS_API_BASE_URL', 'https://www.hsms.ci');
        $baseUrl = preg_replace('#^https?://hsms\.ci#i', 'https://www.hsms.ci', $baseUrl) ?? $baseUrl;
        $url = rtrim($baseUrl, '/') . '/api/envoi-sms/';

        $token = trim((string) env('SMS_API_TOKEN', ''));
        $clientId = trim((string) env('SMS_CLIENT_ID', ''));
        $clientSecret = trim((string) env('SMS_CLIENT_SECRET', ''));

        if ($token === '' || $clientId === '' || $clientSecret === '') {
            Log::warning('HSMS sendMessage skipped: missing SMS credentials');
            throw new \RuntimeException('SMS credentials missing');
        }

        $telephone = preg_replace('/\s+/', '', $to);
        if (is_string($telephone)) {
            $telephone = ltrim($telephone, '+');
        }

        if (is_string($telephone) && str_starts_with($telephone, '0') && strlen($telephone) === 10) {
            $telephone = '225' . $telephone;
        }

        $multipart = [
            ['name' => 'clientid', 'contents' => $clientId],
            ['name' => 'clientsecret', 'contents' => $clientSecret],
            ['name' => 'telephone', 'contents' => (string) $telephone],
            ['name' => 'message', 'contents' => $message],
        ];

        try {
            $verifySsl = filter_var(env('SMS_VERIFY_SSL', true), FILTER_VALIDATE_BOOLEAN);

            $authPrefix = (string) env('SMS_AUTH_PREFIX', 'Bearer');
            $authHeader = trim($authPrefix . ' ' . trim($token));

            Log::info('HSMS sendMessage auth header prepared', [
                'url' => $url,
                'auth_prefix' => $authPrefix,
                'has_token' => $token !== '',
                'has_client_id' => $clientId !== '',
                'has_client_secret' => $clientSecret !== '',
                'token_len' => strlen($token),
                'token_prefix' => substr($token, 0, 6),
                'token_suffix' => substr($token, -6),
                'client_id_len' => strlen($clientId),
                'client_secret_len' => strlen($clientSecret),
            ]);

            $request = Http::timeout((int) env('SMS_TIMEOUT', 10))
                ->withHeaders(['Authorization' => $authHeader])
                ->asMultipart();

            if (!$verifySsl) {
                $request = $request->withoutVerifying();
            }

            $response = $request->post($url, $multipart);

            if (in_array($response->status(), [301, 302, 307, 308], true)) {
                $redirectUrl = (string) $response->header('Location');

                if ($redirectUrl !== '') {
                    $redirectRequest = Http::timeout((int) env('SMS_TIMEOUT', 10))
                        ->withHeaders(['Authorization' => $authHeader])
                        ->asMultipart();

                    if (!$verifySsl) {
                        $redirectRequest = $redirectRequest->withoutVerifying();
                    }

                    $response = $redirectRequest->post($redirectUrl, $multipart);
                }
            }

            if ($response->status() === 401) {
                $fallbackPrefix = strtolower($authPrefix) === 'bearer' ? 'Token' : 'Bearer';
                $fallbackHeader = trim($fallbackPrefix . ' ' . trim($token));

                Log::warning('HSMS sendMessage got 401, retrying with fallback auth prefix', [
                    'auth_prefix' => $authPrefix,
                    'fallback_prefix' => $fallbackPrefix,
                ]);

                $fallbackRequest = Http::timeout((int) env('SMS_TIMEOUT', 10))
                    ->withHeaders(['Authorization' => $fallbackHeader])
                    ->asMultipart();

                if (!$verifySsl) {
                    $fallbackRequest = $fallbackRequest->withoutVerifying();
                }

                $response = $fallbackRequest->post($url, $multipart);
            }

            if (!$response->successful()) {
                Log::error('HSMS sendMessage failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'telephone' => (string) $telephone,
                ]);
            }

            $response->throw();
        } catch (\Throwable $e) {
            Log::error('HSMS sendMessage exception', [
                'message' => $e->getMessage(),
                'telephone' => (string) $telephone,
            ]);
            throw $e;
        }
    }

    /**
     * @return array{
     *     sms_disponibles: int|null,
     *     application: string|null,
     *     wallet_balance: string|null,
     *     wallet_currency: string|null
     * }
     */
    public static function checkBalance(): array
    {
        $baseUrl = (string) env('SMS_API_BASE_URL', 'https://www.hsms.ci');
        $baseUrl = preg_replace('#^https?://hsms\.ci#i', 'https://www.hsms.ci', $baseUrl) ?? $baseUrl;
        $url = rtrim($baseUrl, '/') . '/api/check-sms/';

        $token = trim((string) env('SMS_API_TOKEN', ''));
        $clientId = trim((string) env('SMS_CLIENT_ID', ''));
        $clientSecret = trim((string) env('SMS_CLIENT_SECRET', ''));

        if ($token === '' || $clientId === '' || $clientSecret === '') {
            Log::warning('HSMS checkBalance skipped: missing SMS credentials');
            throw new \RuntimeException('Identifiants SMS manquants dans la configuration.');
        }

        $payload = [
            'clientid' => $clientId,
            'clientsecret' => $clientSecret,
        ];

        try {
            $verifySsl = filter_var(env('SMS_VERIFY_SSL', true), FILTER_VALIDATE_BOOLEAN);
            $authPrefix = (string) env('SMS_AUTH_PREFIX', 'Bearer');
            $authHeader = trim($authPrefix . ' ' . trim($token));

            $request = Http::timeout((int) env('SMS_TIMEOUT', 10))
                ->withHeaders([
                    'Authorization' => $authHeader,
                    'Accept' => 'application/json',
                ])
                ->asJson();

            if (!$verifySsl) {
                $request = $request->withoutVerifying();
            }

            $response = $request->post($url, $payload);

            if (in_array($response->status(), [301, 302, 307, 308], true)) {
                $redirectUrl = (string) $response->header('Location');

                if ($redirectUrl !== '') {
                    $redirectRequest = Http::timeout((int) env('SMS_TIMEOUT', 10))
                        ->withHeaders([
                            'Authorization' => $authHeader,
                            'Accept' => 'application/json',
                        ])
                        ->asJson();

                    if (!$verifySsl) {
                        $redirectRequest = $redirectRequest->withoutVerifying();
                    }

                    $response = $redirectRequest->post($redirectUrl, $payload);
                }
            }

            if ($response->status() === 401) {
                $fallbackPrefix = strtolower($authPrefix) === 'bearer' ? 'Token' : 'Bearer';
                $fallbackHeader = trim($fallbackPrefix . ' ' . trim($token));

                $fallbackRequest = Http::timeout((int) env('SMS_TIMEOUT', 10))
                    ->withHeaders([
                        'Authorization' => $fallbackHeader,
                        'Accept' => 'application/json',
                    ])
                    ->asJson();

                if (!$verifySsl) {
                    $fallbackRequest = $fallbackRequest->withoutVerifying();
                }

                $response = $fallbackRequest->post($url, $payload);
            }

            if (!$response->successful()) {
                $body = $response->json();
                $message = null;
                if (is_array($body)) {
                    $message = $body['message'] ?? $body['detail'] ?? null;
                }
                if (!is_string($message) || $message === '') {
                    $message = $response->body();
                }

                Log::error('HSMS checkBalance failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new \RuntimeException($message !== '' ? $message : 'Impossible de récupérer le solde SMS.');
            }

            $data = $response->json();
            if (!is_array($data)) {
                throw new \RuntimeException('Réponse invalide de l\'API HSMS.');
            }

            return [
                'sms_disponibles' => isset($data['SMS disponibles']) ? (int) $data['SMS disponibles'] : null,
                'application' => isset($data['Application']) ? (string) $data['Application'] : null,
                'wallet_balance' => isset($data['wallet_balance']) ? (string) $data['wallet_balance'] : null,
                'wallet_currency' => isset($data['wallet_currency']) ? (string) $data['wallet_currency'] : null,
            ];
        } catch (\RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('HSMS checkBalance exception', [
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
