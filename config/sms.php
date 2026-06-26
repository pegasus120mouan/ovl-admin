<?php

return [
    'api_base_url' => env('SMS_API_BASE_URL', 'https://hsms.ci'),
    'api_token' => env('SMS_API_TOKEN', ''),
    'client_id' => env('SMS_CLIENT_ID', ''),
    'client_secret' => env('SMS_CLIENT_SECRET', ''),
    'timeout' => (int) env('SMS_TIMEOUT', 10),
    'verify_ssl' => filter_var(env('SMS_VERIFY_SSL', true), FILTER_VALIDATE_BOOLEAN),
    'auth_prefix' => env('SMS_AUTH_PREFIX', 'Bearer'),
    'pin_message_template' => env(
        'SMS_PIN_MESSAGE_TEMPLATE',
        'Bienvenue sur OVLDELIVERY, {nom} {prenoms}. Vos identifiants de connexion sont : {telephone_local} / {pin}. Merci.'
    ),
];
