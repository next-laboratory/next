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

namespace Max\Event\Annotations;

use Attribute;
use Max\Di\Context;
use Max\Di\Contracts\ClassAttribute;
use Max\Di\Exceptions\NotFoundException;
use Max\Event\ListenerProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;

#[Attribute(Attribute::TARGET_CLASS)]
class Listen implements ClassAttribute
{
    /**
     * @param \ReflectionClass $reflectionClass
     *
     * @return void
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function handle(\ReflectionClass $reflectionClass)
    {
        $container = Context::getContainer();
        /** @var ListenerProvider $listenerProvider */
        $listenerProvider = $container->make(ListenerProvider::class);
        $listenerProvider->addListener($container->make($reflectionClass->getName()));
    }
}
