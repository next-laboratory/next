<?php
declare(strict_types=1);

return [
    'default'     => 'redis',
    'connections' => [
        'redis' => [
            'host'          => '127.0.0.1',
            'port'          => 6379,
            'auth'          => '',
            'database'      => 0,
            'timeout'       => 3,
            'readTimeout'   => 3,
            'retryInterval' => 3,
            'reserved'      => '',
            'poolSize'      => 64,
        ],
    ]
];
