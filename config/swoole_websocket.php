<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Room drivers settings
    |--------------------------------------------------------------------------
    */
    'settings' => [

        'table' => [
            'room_rows' => 4096,
            'room_size' => 2048,
            'client_rows' => 8192,
            'client_size' => 2048
        ],

        'redis' => [
            'server' => [
                'host' => env('REDIS_HOST', '127.0.0.1'),
                'password' => env('REDIS_PASSWORD', null),
                'port' => env('REDIS_PORT', 6379),
                'database' => 0,
                'persistent' => true,
            ],
            'options' => [
                //
            ],
            'prefix' => 'swoole:',
        ]
    ],
];
