<?php

use Max\Workerman\Http\Server as HttpServer;
use Max\Workerman\Server;

return [
    'servers' => [
        'http' => [
            'listen' => 'http://127.0.0.1:8989',
            'settings' => [
                'count' => 1,
            ],
            'callbacks' => [
                Server::EVENT_ON_MESSAGE => [HttpServer::class, 'onMessage'],
            ],
        ],
    ],
];