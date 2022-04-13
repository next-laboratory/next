<?php

namespace Max\Foundation\Aspects;

use Closure;
use Max\Di\Annotations\MethodAnnotation;
use Max\Di\Aop\JoinPoint;
use Max\Di\Contracts\AspectInterface;
use Max\Di\Exceptions\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\SimpleCache\CacheInterface;
use ReflectionException as ReflectionExceptionAlias;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Cacheable extends MethodAnnotation implements AspectInterface
{
    /**
     * @var CacheInterface|mixed
     */
    protected CacheInterface $cache;

    /**
     * @param int         $ttl
     * @param string|null $key
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws ReflectionExceptionAlias
     */
    public function __construct(protected int $ttl = 0, protected string $prefix = '', protected ?string $key = null)
    {
        $this->cache = make(CacheInterface::class);
    }

    /**
     * @param JoinPoint $joinPoint
     * @param Closure   $next
     *
     * @return mixed
     */
    public function process(JoinPoint $joinPoint, Closure $next)
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
        $key = $this->key ?? ($joinPoint->getProxy()::class . ':' . $joinPoint->getFunction() . ':' . serialize(array_filter($joinPoint->getArguments(), fn($item) => !is_object($item))));
        return $this->prefix ? ($this->prefix . ':' . $key) : $key;
    }
}