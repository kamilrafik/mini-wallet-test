<?php

return [
    'default' => 'pusher',
    'pusher' => [
        'driver' => env('BROADCAST_DRIVER'),
        'key' => env('PUSHER_APP_KEY'),
        'secret' => env('PUSHER_APP_SECRET'),
        'app_id' => env('PUSHER_APP_ID'),
        'options' => [
            'host' => env('PUSHER_HOST'),
            'port' => env('PUSHER_PORT'),
            'scheme' => env('PUSHER_SCHEME', 'http'),
            'useTLS' => false,
        ],
    ],
];
