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

    'sprintpay' => [
        'key' => env('SPRINTPAY_KEY'),  // generate-virtual-account API + payment redirect URL
        'vtu_base_url' => env('SPRINTPAY_VTU_BASE_URL', 'https://web.sprintpay.online/api'),
        'vtu_key' => env('SPRINTPAY_VTU_KEY'),
        'vtu_secret' => env('SPRINTPAY_VTU_SECRET'),
    ],

    /** HTTP client limits for US-numbers upstream (getatext); raise if you see cURL error 28 timeouts. */
    'getatext' => [
        'timeout' => (int) env('GETATEXT_HTTP_TIMEOUT', 60),
        'connect_timeout' => (int) env('GETATEXT_HTTP_CONNECT_TIMEOUT', 25),
    ],

    /** HTTP client limits for Server 2 (SMSPool); cURL 7 usually means the host cannot reach api.smspool.net:443. */
    'smspool' => [
        'timeout' => (int) env('SMSPOOL_HTTP_TIMEOUT', 30),
        'connect_timeout' => (int) env('SMSPOOL_HTTP_CONNECT_TIMEOUT', 10),
    ],

    /** HTTP client limits for Server 3 (FiveSim). */
    'fivesim' => [
        'timeout' => (int) env('FIVESIM_HTTP_TIMEOUT', 45),
        'connect_timeout' => (int) env('FIVESIM_HTTP_CONNECT_TIMEOUT', 15),
    ],

];
