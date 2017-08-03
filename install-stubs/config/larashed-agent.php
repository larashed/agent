<?php

return [
    'application_id'  => env('LARASHED_APP_ID'),
    'application_key' => env('LARASHED_APP_KEY'),
    'url'             => env('LARASHED_API_URL', 'https://app.larashed.com/api/'),
    'verify-ssl'      => env('LARASHED_API_VERIFY_SSL', true),
    'storage'         => [
        // file, database
        'default' => 'file',
        'engines' => [
            'file'     => [
                'disk' => 'local'
            ],
            'database' => [
                'connection' => 'mysql',
                'table'      => 'larashed_log'
            ]
        ]
    ],
    'auth'            => [
        'guard' => 'web'
    ]
];
