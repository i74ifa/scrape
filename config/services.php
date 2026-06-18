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


    'logo_dev' => [
        'token' => env('LOGO_DEV_TOKEN'),
        'url' => env('LOGO_DEV_URL')
    ],

    '365_dialog' => [
        'api_key' => env('365_DIALOG_API_KEY'),
        'template' => env('365_DIALOG_TEMPLATE'),
        'url' => env('365_DIALOG_URL', 'https://waba.360dialog.io/v1/messages')
    ],

    'fcm' => [
        'project_id' => env('FCM_PROJECT_ID'),
    ],

    'sms_gate' => [
        'username' => env('SMS_GATE_USERNAME'),
        'password' => env('SMS_GATE_PASSWORD'),
        'url' => env('SMS_GATE_URL', 'https://api.sms-gate.app/3rdparty/v1/message'),
    ],
    'google_map' => [
        'key' => env('GOOGLE_MAP_KEY'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI')
    ],
    'telegram' => [
        'bot' => env('TELEGRAM_BOT_NAME'),
        'client_id' => null,
        'client_secret' => env('TELEGRAM_TOKEN'),
        'redirect' => env('TELEGRAM_REDIRECT_URI'),
    ],
    'apple' => [
        'client_id' => env('APPLE_CLIENT_ID'),
        'client_secret' => env('APPLE_CLIENT_SECRET'),
        'private_key' => env('APPLE_PRIVATE_KEY'),
        'redirect' => env('APPLE_REDIRECT_URI'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Massaging Services
    |--------------------------------------------------------------------------
    |
    | Available services: wa-go, m365-dialog
    |
    */

    'massaging' => [
        'default' => env('MASSAGING_DEFAULT', 'wa-go'),
        'providers' => [
            'wa-go' => [
                'username' => env('WAGO_USERNAME'),
                'password' => env('WAGO_PASSWORD'),
                'deviceId' => env('WAGO_DEVICE_ID'),
                'deviceIds' => env('WAGO_DEVICE_IDS', ''),
                'url' => env('WAGO_URL', 'https://bot.talabye.com')
            ],
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Classifier (Teachable Machine)
    |--------------------------------------------------------------------------
    |
    | The persistent Node daemon (model/server.js) that keeps tfjs-node + the
    | model loaded. Start it with: cd model && npm run serve
    |
    */

    'classifier' => [
        'url' => env('MODEL_CLASSIFIER_URL', 'http://127.0.0.1:8765'),
        'connect_timeout' => env('MODEL_CLASSIFIER_CONNECT_TIMEOUT', 1),
        'timeout' => env('MODEL_CLASSIFIER_TIMEOUT', 120),

        // Estimated shipping weight (grams) per predicted category. The keys
        // MUST match the model's labels exactly (see model/metadata.json).
        // Tune these to your real average parcel weights.
        'weights' => [
            'bed'            => 20000,
            'winter-clothes' => 900,
            'Camera'         => 800,
            'monitors'       => 5000,
            'underwear'      => 120,
            'summer-clothes' => 350,
            'phones'         => 350,
            'shoes'          => 900,
            't-shirt'        => 250,
            'dresses'        => 450,
            'laptops'        => 2200,
            'mug'            => 450,
            'mouse'          => 150,
        ],
    ],

];
