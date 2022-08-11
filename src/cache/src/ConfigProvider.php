<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Cache;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'bindings' => [
                'Psr\SimpleCache\CacheInterface'    => 'Max\Cache\Cache',
                'Max\Cache\Contract\CacheInterface' => 'Max\Cache\Cache',
            ],
            'publish'  => [
                [
                    'name'        => 'cache',
                    'source'      => __DIR__ . '/../publish/cache.php',
                    'destination' => dirname(__DIR__, 4) . '/config/cache.php',
                ],
            ],
        ];
    }
}
