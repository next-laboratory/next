<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Cache\Contract;

interface CacheDriverInterface
{
    public function has(string $key): bool;

    public function get(string $key): mixed;

    public function set(string $key, mixed $value, ?int $ttl = null): bool;

    public function clear(): bool;

    public function delete(string $key): bool;

    public function increment(string $key, int $step = 1): bool|int;

    public function decrement(string $key, int $step = 1): bool|int;
}
