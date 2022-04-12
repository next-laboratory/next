<?php
declare(strict_types=1);

namespace Max\Foundation\Cache;

use Max\Config\Repository;
use Max\Di\Exceptions\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;

class Cache extends \Max\Cache\Cache
{
    /**
     * @return static
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public static function __new(Repository $repository)
    {
        $cache = new static($repository->get('cache'));
        $cache->withHandler('default');
        return $cache;
    }
}
