<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

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
