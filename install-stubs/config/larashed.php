<?php

return [
    'application_id'       => env('LARASHED_APP_ID'),
    'application_key'      => env('LARASHED_APP_KEY'),
    'url'                  => env('LARASHED_API_URL', 'https://api.larashed.com/'),
    'verify-ssl'           => env('LARASHED_API_VERIFY_SSL', true),
    /*
    |--------------------------------------------------------------------------
    | Ignored environments
    |--------------------------------------------------------------------------
    |
    | The following configuration defines a list of environments in which Larashed
    | is disabled. Separated by commas.
    |
    */
    'ignored_environments' => env('LARASHED_IGNORED_ENVS', 'testing'),

    /*
    |--------------------------------------------------------------------------
    | Log tracking
    |--------------------------------------------------------------------------
    |
    | Controls log collection
    |
    */
    'logging_enabled'      => env('LARASHED_COLLECT_LOGS', false),

    /*
    |--------------------------------------------------------------------------
    | Ignored endpoints
    |--------------------------------------------------------------------------
    |
    | A list of endpoints which will not be reported
    |
    */
    'ignored_endpoints'    => [
        '/larashed/health-check',
    ],

    /*
    |--------------------------------------------------------------------------
    | Larashed data directory
    |--------------------------------------------------------------------------
    |
    | Directory in which Larashed will store it's data
    |
    */
    'directory'            => storage_path('app/larashed'),

    /*
    |--------------------------------------------------------------------------
    | Transport
    |--------------------------------------------------------------------------
    |
    | The following configuration defines how the agent will send the tracking data.
    |
    */
    'transport'            => [
        'default' => env('LARASHED_TRANSPORT', 'unix'),

        'engines' => [
            'unix' => [
                // filename for the unix socket within larashed.directory value
                'address' => env('LARASHED_SOCKET_DIR', '.')
            ],
            'tcp'  => [
                'address' => env('LARASHED_TCP_ADDRESS', '127.0.0.1:33101'),
            ]
        ]
    ],
    /*
    |--------------------------------------------------------------------------
    | User information tracking
    |--------------------------------------------------------------------------
    |
    | This setting tells us which guard to use for tracking your user names and IDs.
    | Leave this null if you'd like to fallback to config('auth.defaults.guard')
    | or you can disable it completely
    |
    */
    'user'                 => [
        'enabled' => env('LARASHED_COLLECT_USER_DATA', true),
        'guard'   => null
    ]
];
