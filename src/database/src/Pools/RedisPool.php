<?php

declare(strict_types=1);

/**
 * This file is part of the Max package.
 *
 * (c) Cheng Yao <987861463@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Max\Database\Pools;

use Max\Config\Repository;
use Swoole\Database\RedisConfig;
use Swoole\Database\RedisPool as SwooleRedisPool;

class RedisPool
{
    /**
     * Redis connection pools.
     *
     * @var SwooleRedisPool[]
     */
    protected static array $pool = [];

    /**
     * Default config for redis connections.
     */
    protected const DEFAULT_REDIS_CONFIG = [
        'host'          => '127.0.0.1',
        'port'          => 6379,
        'auth'          => '',
        'database'      => 0,
        'timeout'       => 3,
        'readTimeout'   => 3,
        'retryInterval' => 3,
        'reserved'      => '',
        'poolSize'      => 64,
    ];

    /**
     * @param array $config
     */
    public function __construct(protected array $config)
    {
    }

    /**
     * @param Repository $repository
     *
     * @return static
     */
    public static function __new(Repository $repository): static
    {
        return new static($repository->get('redis'));
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasPool(string $name): bool
    {
        return isset(self::$pool[$name]);
    }

    /**
     * @param string|null $name
     *
     * @return SwooleRedisPool
     */
    public function getPool(?string $name): SwooleRedisPool
    {
        $name ??= $this->config['default'];
        if (!$this->hasPool($name)) {
            $config            = array_replace_recursive(self::DEFAULT_REDIS_CONFIG, $this->config['connections'][$name] ?? []);
            self::$pool[$name] = new SwooleRedisPool((new RedisConfig())
                ->withHost($config['host'])
                ->withPort($config['port'])
                ->withAuth($config['auth'])
                ->withDbIndex($config['database'])
                ->withReadTimeout($config['readTimeout'])
                ->withRetryInterval($config['retryInterval'])
                ->withTimeout($config['timeout'])
                ->withReserved($config['reserved']));
        }

        return self::$pool[$name];
    }
}
