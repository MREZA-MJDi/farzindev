<?php

return [
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
        'email' => env('ADMIN_EMAIL', 'admin@farajihome.com'),
        'password' => env('ADMIN_PASSWORD'),
    ],
];
