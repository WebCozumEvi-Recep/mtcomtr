<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    | Cloudflare DNS (provision / finalize)
    | dns_proxied: true = turuncu bulut (varsayılan), false = doğrudan DNS (curl örneğinizle uyumlu)
    | dns_ttl: proxied false iken saniye (örn. 3600); proxied true iken API için 1 (auto) kullanılır
    */
    'cloudflare' => [
        'token' => env('CLOUDFLARE_TOKEN'),
        'server_ip' => env('SERVER_IP'),
        'dns_proxied' => env('CLOUDFLARE_DNS_PROXIED', true),
        'dns_ttl' => env('CLOUDFLARE_DNS_TTL', 3600),
    ],

];
