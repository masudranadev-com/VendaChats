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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'meta' => [
        'verify_token' => env('META_VERIFY_TOKEN'),
        'page_access_token' => env('META_PAGE_ACCESS_TOKEN', env('META_ACCESS_TOKEN')),
        'graph_api_version' => env('META_GRAPH_API_VERSION', 'v22.0'),
    ],
    'backend' => [
        'url' => env('BACKEND_API_URL', 'http://localhost:8082'),
    ],

    'facebook' => [
        'app_id' => env('FB_APP_ID'),
        'app_secret' => env('FB_APP_SECRET'),
        'redirect_uri' => env('FB_REDIRECT_URI', env('APP_URL').'/facebook/auth/callback'),
        'graph_version' => env('FB_GRAPH_VERSION', 'v22.0'),
        'verify_token' => env('FB_VERIFY_TOKEN', 'myverifytoken123'),
        'scopes' => [
            'public_profile',
            'email',
            'pages_show_list',
            'pages_read_engagement',
            'pages_manage_engagement',
            'pages_manage_metadata',
            'pages_messaging',
        ],
    ],
];
