<?php

declare(strict_types=1);

/**
 * This file is part of the Max package.
 *
 * (c) Cheng Yao <987861463@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Max\Cache\Aspects;

use Closure;
use Max\Aop\Contracts\AspectInterface;
use Max\Aop\JoinPoint;
use Psr\Container\ContainerExceptionInterface;
use Psr\SimpleCache\CacheInterface;
use ReflectionException;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Cacheable implements AspectInterface
{
    public function __construct(
        protected int     $ttl = 0,
        protected string  $prefix = '',
        protected ?string $key = null
    )
    {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function process(JoinPoint $joinPoint, Closure $next): mixed
    {
        $cache = make(CacheInterface::class);
        return $cache->remember($this->getKey($joinPoint), fn() => $next($joinPoint), $this->ttl);
    }

    protected function getKey(JoinPoint $joinPoint): string
    {
        $key = $this->key ?? ($joinPoint->object::class . ':' . $joinPoint->method . ':' . serialize(array_filter($joinPoint->parameters, fn($item) => !is_object($item))));
        return $this->prefix ? ($this->prefix . ':' . $key) : $key;
    }
}
