<?php

return [
    'application_id'  => env('LARASHED_APP_ID'),
    'application_key' => env('LARASHED_APP_KEY'),
    'url'             => env('LARASHED_API_URL', 'https://api.larashed.com/'),
    'verify-ssl'      => env('LARASHED_API_VERIFY_SSL', true),
    'storage'         => [
        'default' => 'file',
        'engines' => [
            'file' => [
                'disk'      => 'local',
                'directory' => 'larashed/monitoring'
            ]
        ]
    ],
    'auth'            => [
        'guard' => 'web'
    ],
    'enabled'         => env('LARASHED_ENABLED', true)
];
