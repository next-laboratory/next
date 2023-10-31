<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Cache\Driver;

class ApcuDriver extends AbstractDriver
{
    public function has(string $key): bool
    {
        return (bool) \apcu_exists($key);
    }

    public function get(string $key): mixed
    {
        $data = \apcu_fetch($key, $success);
        return $success === true ? $data : null;
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        return (bool) \apcu_store($key, $value, (int) $ttl);
    }

    public function clear(): bool
    {
        return \apcu_clear_cache('user');
    }

    public function delete(string $key): bool
    {
        return (bool) \apcu_delete($key);
    }

    public function increment(string $key, int $step = 1): int|bool
    {
        return \apcu_inc($key, $step);
    }

    public function decrement(string $key, int $step = 1): int|bool
    {
        return \apcu_dec($key, $step);
    }
}
