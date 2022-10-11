<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\JsonRpc;

use Max\Aop\Collector\AbstractCollector;
use Max\JsonRpc\Attribute\RpcService;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;

class ServiceCollector extends AbstractCollector
{
    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public static function collectClass(string $class, object $attribute): void
    {
        if ($attribute instanceof RpcService) {
            $service = $attribute->name;
            make(Server::class)->register($service, $class);
        }
    }
}
