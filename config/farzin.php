<?php

return [
    'admin_path' => (preg_replace('/[^A-Za-z0-9_-]/', '', trim((string) env('ADMIN_PATH', 'admin'), '/')) ?: 'admin'),

    /*
    |--------------------------------------------------------------------------
    | Initial administrator
    |--------------------------------------------------------------------------
    |
    | Values are read into the configuration repository so the initial admin
    | can be seeded safely even after config:cache has been generated.
    |
    */
    'admin' => [
        'name' => env('ADMIN_NAME', 'Farzin Admin'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],
];
