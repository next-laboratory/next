<?php

namespace Max\Cache\Contracts;

use Closure;
use Psr\SimpleCache\CacheInterface as PsrCacheInterface;

interface CacheInterface extends PsrCacheInterface
{
    public function remember($key, Closure $callback, ?int $ttl = null): mixed;

    public function incr($key, int $step = 1): bool;

    public function decr($key, int $step = 1): bool;

    public function pull($key): mixed;
}
