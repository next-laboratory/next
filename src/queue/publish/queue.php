<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

return [
    'default'     => 'redis',     // 默认连接
    'sleep'       => 0.4,           // 异常时候等待时长/秒
    'connections' => [
        'redis' => [
            'driver' => 'Max\Queue\Queue\Redis',
            'config' => [
                'host'     => '127.0.0.1',
                'port'     => 6379,
                'pass'     => '',
                'database' => 1,
            ],
        ],
    ],
];
