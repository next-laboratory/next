<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Cache\Contract;

use Closure;
use Psr\SimpleCache\CacheInterface as PsrCacheInterface;

interface CacheInterface extends PsrCacheInterface
{
    public function remember(string $key, Closure $callback, ?int $ttl = null): mixed;

    public function increment(string $key, int $step = 1): int;

    public function decrement(string $key, int $step = 1): int;

    public function pull(string $key): mixed;
}
