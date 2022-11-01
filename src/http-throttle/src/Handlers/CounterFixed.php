<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Http\Throttle\Handlers;

use Psr\SimpleCache\CacheInterface;

/**
 * 计数器固定窗口算法
 * Class CounterFixed.
 */
class CounterFixed extends ThrottleAbstract
{
    /**
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function allowRequest(string $key, float $micronow, int $max_requests, int $duration, CacheInterface $cache): bool
    {
        $cur_requests          = $cache->get($key, 0);
        $now                   = (int) $micronow;
        $wait_reset_seconds    = $duration - $now    % $duration;     // 距离下次重置还有n秒时间
        $this->waitSeconds     = $wait_reset_seconds % $duration  + 1;
        $this->currentRequests = $cur_requests;

        if ($cur_requests < $max_requests) {   // 允许访问
            $cache->set($key, $this->currentRequests + 1, $wait_reset_seconds);
            return true;
        }

        return false;
    }
}
