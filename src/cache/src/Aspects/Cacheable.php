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
use Max\Aop\JoinPoint;
use Max\Di\Context;
use Max\Aop\Contracts\AspectInterface;
use Max\Di\Exceptions\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\SimpleCache\CacheInterface;
use ReflectionException as ReflectionExceptionAlias;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Cacheable implements AspectInterface
{
    /**
     * @var CacheInterface|mixed
     */
    protected CacheInterface $cache;

    /**
     * @param int         $ttl
     * @param string      $prefix
     * @param string|null $key
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws ReflectionExceptionAlias
     */
    public function __construct(
        protected int     $ttl = 0,
        protected string  $prefix = '',
        protected ?string $key = null
    )
    {
        $this->cache = Context::getContainer()->make(CacheInterface::class);
    }

    /**
     * @param JoinPoint $joinPoint
     * @param Closure   $next
     *
     * @return mixed
     */
    public function process(JoinPoint $joinPoint, Closure $next): mixed
    {
        return $this->cache->remember($this->getKey($joinPoint), fn() => $next($joinPoint), $this->ttl);
    }

    /**
     * @param JoinPoint $joinPoint
     *
     * @return string
     */
    protected function getKey(JoinPoint $joinPoint): string
    {
        $key = $this->key ?? ($joinPoint->object::class . ':' . $joinPoint->method . ':' . serialize(array_filter($joinPoint->parameters, fn($item) => !is_object($item))));
        return $this->prefix ? ($this->prefix . ':' . $key) : $key;
    }
}
