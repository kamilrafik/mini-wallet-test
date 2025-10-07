<?php

return [
    'pusher' => [
        'driver' => env('BROADCAST_DRIVER'),
        'key' => env('PUSHER_APP_KEY'),
        'secret' => env('PUSHER_APP_SECRET'),
        'app_id' => env('PUSHER_APP_ID'),
        'options' => [
            'useTLS' => false,
        ],
    ],
];
