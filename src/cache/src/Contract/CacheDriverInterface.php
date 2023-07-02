<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Cache\Contract;

interface CacheDriverInterface
{
    public function has(string $key): bool;

    public function get(string $key): mixed;

    public function set(string $key, mixed $value, ?int $ttl = null): bool;

    public function clear(): bool;

    public function delete(string $key): bool;

    public function increment(string $key, int $step = 1): int|bool;

    public function decrement(string $key, int $step = 1): int|bool;
}
