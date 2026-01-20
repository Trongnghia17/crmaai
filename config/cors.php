<?php


return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', '*'],

    'allowed_methods' => ['*'],

    // In production, prefer restricting the allowed origins to your frontend domains.
    // Example: allow both http and https variants of the frontend domain used by your app.
    'allowed_origins' => [
        'http://localhost:8080',
        'http://127.0.0.1:8080',
        'http://crm.aaipharma.vn',
        'https://crm.aaipharma.vn',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // If your frontend needs to send cookies or use `withCredentials`, set this to true.
    // Note: when supports_credentials is true you must list explicit origins (can't use '*').
    'supports_credentials' => false,

];
