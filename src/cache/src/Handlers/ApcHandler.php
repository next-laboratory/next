<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Cache\Handlers;

use function apc_clear_cache;
use function apc_dec;
use function apc_delete;
use function apc_exists;
use function apc_fetch;
use function apc_inc;
use function apc_store;

class ApcHandler extends CacheHandler
{
    /**
     * {@inheritDoc}
     */
    public function incr(string $key, int $step = 1): bool
    {
        return apc_inc($key, $step);
    }

    /**
     * {@inheritDoc}
     */
    public function decr(string $key, int $step = 1): bool
    {
        return apc_dec($key, $step);
    }

    /**
     * {@inheritDoc}
     */
    public function get($key, $default = null)
    {
        $data = apc_fetch($key, $success);
        return $success === true ? $data : $default;
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $value, $ttl = null)
    {
        return apc_store($key, $value, $ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($key)
    {
        return apc_delete($key);
    }

    /**
     * {@inheritDoc}
     */
    public function clear(): bool
    {
        return apc_clear_cache('user');
    }

    /**
     * {@inheritDoc}
     */
    public function has($key)
    {
        return apc_exists($key);
    }
}
