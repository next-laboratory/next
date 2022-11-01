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
 * 漏桶算法
 * Class LeakyBucket.
 */
class LeakyBucket extends ThrottleAbstract
{
    public function allowRequest(string $key, float $micronow, int $max_requests, int $duration, CacheInterface $cache): bool
    {
        if ($max_requests <= 0) {
            return false;
        }

        $last_time = $cache->get($key, 0);                   // 最近一次请求
        $rate      = (float) $duration / $max_requests;       // 平均 n 秒一个请求
        if ($micronow - $last_time < $rate) {
            $this->currentRequests = 1;
            $this->waitSeconds     = ceil($rate - ($micronow - $last_time));
            return false;
        }

        $cache->set($key, $micronow, $duration);
        return true;
    }
}
