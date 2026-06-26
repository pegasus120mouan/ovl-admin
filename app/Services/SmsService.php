<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public static function sendPin(string $to, string $pin, ?string $nom = null, ?string $prenoms = null): void
    {
        ['token' => $token, 'client_id' => $clientId, 'client_secret' => $clientSecret] = self::credentials();
        $url = self::apiUrl('/api/envoi-sms/');

        $telephone = self::normalizePhone($to);

        $telephoneLocal = (string) $telephone;
        if (str_starts_with($telephoneLocal, '225')) {
            $telephoneLocal = substr($telephoneLocal, 3);
        }

        $template = (string) config('sms.pin_message_template');

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
            $authPrefix = (string) config('sms.auth_prefix', 'Bearer');
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

            $response = self::postMultipart($url, $multipart, $authHeader);

            if ($response->status() === 401) {
                $fallbackPrefix = strtolower($authPrefix) === 'bearer' ? 'Token' : 'Bearer';
                $fallbackHeader = trim($fallbackPrefix . ' ' . trim($token));

                Log::warning('HSMS sendPin got 401, retrying with fallback auth prefix', [
                    'auth_prefix' => $authPrefix,
                    'fallback_prefix' => $fallbackPrefix,
                ]);

                $response = self::postMultipart($url, $multipart, $fallbackHeader);
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
        ['token' => $token, 'client_id' => $clientId, 'client_secret' => $clientSecret] = self::credentials();
        $url = self::apiUrl('/api/envoi-sms/');
        $telephone = self::normalizePhone($to);

        $multipart = [
            ['name' => 'clientid', 'contents' => $clientId],
            ['name' => 'clientsecret', 'contents' => $clientSecret],
            ['name' => 'telephone', 'contents' => (string) $telephone],
            ['name' => 'message', 'contents' => $message],
        ];

        try {
            $authPrefix = (string) config('sms.auth_prefix', 'Bearer');
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

            $response = self::postMultipart($url, $multipart, $authHeader);

            if ($response->status() === 401) {
                $fallbackPrefix = strtolower($authPrefix) === 'bearer' ? 'Token' : 'Bearer';
                $fallbackHeader = trim($fallbackPrefix . ' ' . trim($token));

                Log::warning('HSMS sendMessage got 401, retrying with fallback auth prefix', [
                    'auth_prefix' => $authPrefix,
                    'fallback_prefix' => $fallbackPrefix,
                ]);

                $response = self::postMultipart($url, $multipart, $fallbackHeader);
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
        ['token' => $token, 'client_id' => $clientId, 'client_secret' => $clientSecret] = self::credentials();
        $url = self::apiUrl('/api/check-sms/');

        $payload = [
            'clientid' => $clientId,
            'clientsecret' => $clientSecret,
        ];

        try {
            $authPrefix = (string) config('sms.auth_prefix', 'Bearer');
            $authHeader = trim($authPrefix . ' ' . trim($token));

            $response = self::postJson($url, $payload, $authHeader);

            if ($response->status() === 401) {
                $fallbackPrefix = strtolower($authPrefix) === 'bearer' ? 'Token' : 'Bearer';
                $fallbackHeader = trim($fallbackPrefix . ' ' . trim($token));
                $response = self::postJson($url, $payload, $fallbackHeader);
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

    /**
     * @return array{token: string, client_id: string, client_secret: string}
     */
    private static function credentials(): array
    {
        $token = trim((string) config('sms.api_token', ''));
        $clientId = trim((string) config('sms.client_id', ''));
        $clientSecret = trim((string) config('sms.client_secret', ''));

        if ($token === '' || $clientId === '' || $clientSecret === '') {
            Log::warning('HSMS skipped: missing SMS credentials');
            throw new \RuntimeException('Identifiants SMS manquants dans la configuration.');
        }

        return [
            'token' => $token,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ];
    }

    private static function apiUrl(string $path): string
    {
        $baseUrl = (string) config('sms.api_base_url', 'https://hsms.ci');
        $baseUrl = preg_replace('#^https?://hsms\.ci#i', 'https://www.hsms.ci', $baseUrl) ?? $baseUrl;

        return rtrim($baseUrl, '/') . $path;
    }

    private static function normalizePhone(string $to): string
    {
        $telephone = preg_replace('/\s+/', '', $to);
        $telephone = ltrim((string) $telephone, '+');

        if (str_starts_with($telephone, '0') && strlen($telephone) === 10) {
            $telephone = '225' . $telephone;
        }

        return $telephone;
    }

    private static function httpClient()
    {
        $request = Http::timeout((int) config('sms.timeout', 10));

        if (!config('sms.verify_ssl', true)) {
            $request = $request->withoutVerifying();
        }

        return $request;
    }

    private static function postMultipart(string $url, array $multipart, string $authHeader)
    {
        $response = self::httpClient()
            ->withHeaders(['Authorization' => $authHeader])
            ->asMultipart()
            ->post($url, $multipart);

        if (in_array($response->status(), [301, 302, 307, 308], true)) {
            $redirectUrl = (string) $response->header('Location');

            if ($redirectUrl !== '') {
                $response = self::httpClient()
                    ->withHeaders(['Authorization' => $authHeader])
                    ->asMultipart()
                    ->post($redirectUrl, $multipart);
            }
        }

        return $response;
    }

    private static function postJson(string $url, array $payload, string $authHeader)
    {
        $response = self::httpClient()
            ->withHeaders([
                'Authorization' => $authHeader,
                'Accept' => 'application/json',
            ])
            ->asJson()
            ->post($url, $payload);

        if (in_array($response->status(), [301, 302, 307, 308], true)) {
            $redirectUrl = (string) $response->header('Location');

            if ($redirectUrl !== '') {
                $response = self::httpClient()
                    ->withHeaders([
                        'Authorization' => $authHeader,
                        'Accept' => 'application/json',
                    ])
                    ->asJson()
                    ->post($redirectUrl, $payload);
            }
        }

        return $response;
    }
}
