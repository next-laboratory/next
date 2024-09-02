<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Foundation\Cache\Aspect;

use Next\Aop\Contract\AspectInterface;
use Next\Aop\ProceedingJoinPoint;
use Psr\Container\ContainerExceptionInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Cacheable implements AspectInterface
{
    public function __construct(
        protected string  $prefix = '',
        protected ?string $key = null,
        protected ?int    $ttl = null,
    )
    {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException|\ReflectionException
     */
    public function process(ProceedingJoinPoint $joinPoint): mixed
    {
        return make(CacheInterface::class)->remember($this->getKey($joinPoint), fn() => $joinPoint->process(), $this->ttl);
    }

    protected function getKey(ProceedingJoinPoint $joinPoint): string
    {
        $key = $this->key ?? ($joinPoint->class . ':' . $joinPoint->method . ':' . serialize(array_filter($joinPoint->parameters->getArrayCopy(), fn($item) => !is_object($item))));
        return $this->prefix ? ($this->prefix . ':' . $key) : $key;
    }
}
