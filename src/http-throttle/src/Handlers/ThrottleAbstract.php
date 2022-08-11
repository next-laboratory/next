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

abstract class ThrottleAbstract
{
    /**
     * @var int 当前已有的请求数
     */
    protected int $currentRequests = 0;

    /**
     * @var int 距离下次合法请求还有多少秒
     */
    protected int $waitSeconds = 0;

    /**
     * 是否允许访问.
     *
     * @param string         $key          缓存键
     * @param float          $micronow     当前时间戳,可含毫秒
     * @param int            $max_requests 允许最大请求数
     * @param int            $duration     限流时长
     * @param CacheInterface $cache        缓存对象
     */
    abstract public function allowRequest(string $key, float $micronow, int $max_requests, int $duration, CacheInterface $cache): bool;

    /**
     * 计算距离下次合法请求还有多少秒.
     */
    public function getWaitSeconds(): int
    {
        return $this->waitSeconds;
    }

    /**
     * 当前已有的请求数.
     */
    public function getCurRequests(): int
    {
        return $this->currentRequests;
    }
}
