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
use Psr\SimpleCache\InvalidArgumentException;

/**
 * 计数器滑动窗口算法
 * Class CouterSlider.
 */
class CounterSlider extends ThrottleAbstract
{
    /**
     * @throws InvalidArgumentException
     */
    public function allowRequest(string $key, float $micronow, int $max_requests, int $duration, CacheInterface $cache): bool
    {
        $history = (array) $cache->get($key, []);
        $now     = (int) $micronow;
        // 移除过期的请求的记录
        $history = array_values(array_filter($history, function ($val) use ($now, $duration) {
            return $val >= $now - $duration;
        }));

        $this->currentRequests = count($history);
        if ($this->currentRequests < $max_requests) {
            // 允许访问
            $history[] = $now;
            $cache->set($key, $history, $duration);
            return true;
        }

        if ($history) {
            $waitSeconds       = $duration - ($now - $history[0]) + 1;
            $this->waitSeconds = max($waitSeconds, 0);
        }

        return false;
    }
}
