<?php

return [
    'base_url' => env('SHIPROCKET_API_URL', env('SHIPROCKET_BASE_URL', 'https://apiv2.shiprocket.in/v1/external')),
    'email' => env('SHIPROCKET_EMAIL'),
    'password' => env('SHIPROCKET_PASSWORD'),

    // Optional API key/secret flow (if enabled for your account). If present, token requests will include them.
    'api_key' => env('SHIPROCKET_API_KEY'),
    'api_secret' => env('SHIPROCKET_API_SECRET'),

    // Pickup location nickname configured in Shiprocket (required for shipment creation).
    'pickup_location' => env('SHIPROCKET_PICKUP_LOCATION', 'Primary'),

    // Shipment defaults
    'default_weight' => env('SHIPROCKET_DEFAULT_WEIGHT', 0.5), // in KG
    'default_length' => env('SHIPROCKET_DEFAULT_LENGTH', 10), // in CM
    'default_breadth' => env('SHIPROCKET_DEFAULT_BREADTH', 10),
    'default_height' => env('SHIPROCKET_DEFAULT_HEIGHT', 5),

    // Behaviour toggles
    'auto_push_on_paid' => env('SHIPROCKET_AUTO_PUSH_ON_PAID', false),
    'log_channel' => env('SHIPROCKET_LOG_CHANNEL', 'stack'),
    'timeout' => env('SHIPROCKET_TIMEOUT', 30),

    // Webhook token (shared secret) to validate inbound hooks.
    'webhook_token' => env('SHIPROCKET_WEBHOOK_TOKEN'),
];
