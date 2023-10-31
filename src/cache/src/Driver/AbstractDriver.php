<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Cache\Driver;

use Next\Cache\Contract\CacheDriverInterface;

abstract class AbstractDriver implements CacheDriverInterface
{
    public function increment(string $key, int $step = 1): int|bool
    {
        $value = (int) $this->get($key) + $step;
        $this->set($key, $value);
        return $value;
    }

    public function decrement(string $key, int $step = 1): int|bool
    {
        return $this->increment($key, -$step);
    }
}
