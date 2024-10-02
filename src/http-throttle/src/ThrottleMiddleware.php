<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Next\Http\Throttle;

use Next\Http\Throttle\Handlers\ThrottleAbstract;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface;

class ThrottleMiddleware implements MiddlewareInterface
{
    protected string $prefix = 'throttle:';

    public static array $duration = [
        's' => 1,
        'm' => 60,
        'h' => 3600,
        'd' => 86400,
    ];

    protected int $waitSeconds = 0;             // 下次合法请求还有多少秒
    protected int $now = 0;             // 当前时间戳
    protected int $max_requests = 0;             // 规定时间内允许的最大请求次数
    protected int $expire = 0;             // 规定时间
    protected int $remaining = 0;             // 规定时间内还能请求的次数

    public function __construct(
        protected CacheInterface   $cache,
        protected ThrottleAbstract $handler,
    )
    {
    }

    /**
     * @throws RateLimitException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->allowRequest($request)) {
//            $response->withHeader('Retry-After', $waitSeconds);
            throw new RateLimitException('Too Many Requests', 429);
        }
        $response = $handler->handle($request);
        if (200 <= $response->getStatusCode() && 300 > $response->getStatusCode()) {
            // 将速率限制 headers 添加到响应中
            $response = $response
                ->withHeader('X-Rate-Limit-Limit', $this->max_requests)
                ->withHeader('X-Rate-Limit-Remaining', $this->remaining < 0 ? 0 : $this->remaining)
                ->withHeader('X-Rate-Limit-Reset', $this->now + $this->expire);
        }

        return $response;
    }

    /**
     * 请求是否允许.
     */
    protected function allowRequest(ServerRequestInterface $request): bool
    {
        if (!in_array($request->getMethod(), $this->getEnableMethods())) {
            return true;
        }

        $key = $this->getCacheKey($request);
        if ($key === null) {
            return true;
        }

        [$max_requests, $duration] = $this->parseRate($this->getVisitRate());

        $micronow = microtime(true);
        $now = (int)$micronow;

        $allow = $this->handler->allowRequest($key, $micronow, $max_requests, $duration, $this->cache);

        if ($allow) {
            // 允许访问
            $this->now = $now;
            $this->expire = $duration;
            $this->max_requests = $max_requests;
            $this->remaining = $max_requests - $this->handler->getCurRequests();
            return true;
        }

        $this->waitSeconds = $this->handler->getWaitSeconds();
        return false;
    }

    /**
     * 生成缓存的 key.
     */
    protected function getCacheKey(ServerRequestInterface $request): ?string
    {
        return md5(serialize([
            'path' => $request->getUri()->getPath(),
            'ip' => $this->getRealIp($request),
        ]));
    }

    protected function getRealIp(ServerRequestInterface $request)
    {
        $headers = $request->getHeaders();
        if (!empty($headers['x-forwarded-for'][0])) {
            return $headers['x-forwarded-for'][0];
        }
        if (!empty($headers['x-real-ip'][0])) {
            return $headers['x-real-ip'][0];
        }
        $serverParams = $request->getServerParams();

        return $serverParams['remote_addr'] ?? '';
    }

    /**
     * 解析频率配置项.
     *
     * @return int[]
     */
    protected function parseRate(string $rate): array
    {
        [$num, $period] = explode('/', $rate);
        $max_requests = (int)$num;
        $duration = static::$duration[$period] ?? (int)$period;
        return [$max_requests, $duration];
    }

    /**
     * 节流频率 null 表示不限制 eg: 10/m  20/h  300/d
     */
    protected function getVisitRate(): string
    {
        return '10/m';
    }

    protected function getEnableMethods(): array
    {
        return ['GET', 'HEAD'];
    }
}
