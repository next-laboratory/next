<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Redis;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'publish' => [
                [
                    'name'        => 'redis',
                    'source'      => __DIR__ . '/../publish/redis.php',
                    'destination' => dirname(__DIR__, 4) . '/config/redis.php',
                ],
            ],
        ];
    }
}
