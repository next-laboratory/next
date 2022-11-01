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
 * 令牌桶算法
 * Class TokenBucket.
 */
class TokenBucket extends ThrottleAbstract
{
    /**
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function allowRequest(string $key, float $micronow, int $max_requests, int $duration, CacheInterface $cache): bool
    {
        if ($max_requests <= 0 || $duration <= 0) {
            return false;
        }

        $assist_key = $key . 'store_num';              // 辅助缓存
        $rate       = (float) $max_requests / $duration;     // 平均一秒生成 n 个 token

        $last_time = $cache->get($key, null);
        $store_num = $cache->get($assist_key, null);

        if ($last_time === null || $store_num === null) {      // 首次访问
            $cache->set($key, $micronow, $duration);
            $cache->set($assist_key, $max_requests - 1, $duration);
            return true;
        }

        $create_num = floor(($micronow - $last_time) * $rate);              // 推算生成的 token 数
        $token_left = (int) min($max_requests, $store_num + $create_num);  // 当前剩余 tokens 数量

        if ($token_left < 1) {
            $tmp               = (int) ceil($duration / $max_requests);
            $this->waitSeconds = $tmp - (int) ($micronow - $last_time) % $tmp;
            return false;
        }
        $this->currentRequests = $max_requests - $token_left;
        $cache->set($key, $micronow, $duration);
        $cache->set($assist_key, $token_left - 1, $duration);
        return true;
    }
}
