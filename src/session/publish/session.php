<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

return [
    'handler' => 'Max\Session\Handler\FileHandler',
    'config'  => [
        'path'          => __DIR__ . '/../runtime/session',
        'gcDivisor'     => 100,
        'gcProbability' => 1,
        'gcMaxLifetime' => 1440,
    ],
    //    'handler' => 'Max\Session\Handler\RedisHandler',
    //    'config'  => [
    //        'connector' => 'Max\Redis\Connector\BaseConnector',
    //        'prefix'  => 'PHP_SESS:',
    //        'host'      => '127.0.0.1',
    //        'port'      => 6379,
    //        'expire'    => 3600,
    //    ],
];
