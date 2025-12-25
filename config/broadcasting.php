<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster
    |--------------------------------------------------------------------------
    |
    | This option controls the default broadcaster that will be used by the
    | framework when an event needs to be broadcast. You may set this to
    | any of the connections defined in the "connections" array below.
    |
    */

    'default' => env('BROADCAST_DRIVER', 'pusher'),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the broadcast connections that will be used
    | to broadcast events to other systems or over websockets. Samples of
    | each available type of connection are provided inside this array.
    |
    */

    'connections' => [

        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY', 'd7844fc467464fad6f63'),
            'secret' => env('PUSHER_APP_SECRET', '0cc84702eff4731d5823'),
            'app_id' => env('PUSHER_APP_ID', '2073677'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER', 'ap1'),
                'useTLS' => true,
                'host' => 'api-' . env('PUSHER_APP_CLUSTER', 'ap1') . '.pusher.com',
                'port' => 443,
                'scheme' => 'https'
            ],
        ],

        'ably' => [
            'driver' => 'ably',
            'key' => env('ABLY_KEY'),
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

];
