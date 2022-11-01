<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

return [
    'default'     => 'redis',       // 默认连接
    'sleep'       => 400,           // 异常时候等待时长/ms
    'connections' => [
        'redis' => [
            'driver' => 'Max\AsyncQueue\Driver\Redis',
            'config' => [
                'connector' => \Max\Redis\Connector\BasePoolConnector::class,
                'host'      => '127.0.0.1',
                'port'      => 6379,
                'pass'      => '',
                'database'  => 1,
            ],
        ],
    ],
];
