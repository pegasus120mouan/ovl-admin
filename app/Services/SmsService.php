<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public static function sendPin(string $to, string $pin, ?string $nom = null, ?string $prenoms = null): void
    {
        $baseUrl = (string) env('SMS_API_BASE_URL', 'https://hsms.ci');
        $url = rtrim($baseUrl, '/') . '/api/envoi-sms/';

        $token = (string) env('SMS_API_TOKEN', '');
        $clientId = (string) env('SMS_CLIENT_ID', '');
        $clientSecret = (string) env('SMS_CLIENT_SECRET', '');

        if ($token === '' || $clientId === '' || $clientSecret === '') {
            return;
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

            $request = Http::timeout((int) env('SMS_TIMEOUT', 10))
                ->withToken($token)
                ->asMultipart();

            if (!$verifySsl) {
                $request = $request->withoutVerifying();
            }

            $response = $request->post($url, $multipart);

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
}
