<?php

return [
    'application_id'  => env('LARASHED_APP_ID'),
    'application_key' => env('LARASHED_APP_KEY'),
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
