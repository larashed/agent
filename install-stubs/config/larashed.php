<?php

return [
    'application_id'  => env('LARASHED_APP_ID'),
    'application_key' => env('LARASHED_APP_KEY'),
    'url'             => env('LARASHED_API_URL', 'https://api.larashed.com/'),
    'verify-ssl'      => env('LARASHED_API_VERIFY_SSL', true),

    /*
    |--------------------------------------------------------------------------
    | Storage
    |--------------------------------------------------------------------------
    |
    | The following configuration defines how the agent will store tracking data.
    |
    */
    'storage'         => [
        'default' => 'file',
        'engines' => [
            'file' => [
                'disk'      => 'local',
                'directory' => 'larashed/monitoring'
            ]
        ]
    ],
    /*
    |--------------------------------------------------------------------------
    | Restart file
    |--------------------------------------------------------------------------
    |
    | Used for letting the daemon know that it needs to be killed
    |
    */
    'restart-file' => 'larashed/restart-file',
    /*
    |--------------------------------------------------------------------------
    | Default guard
    |--------------------------------------------------------------------------
    |
    | This setting tells us which guard to use for tracking your user names and IDs.
    | Leave this null if you'd like to fallback to config('auth.defaults.guard')
    |
    */
    'auth'            => [
        'guard' => null
    ]
];
