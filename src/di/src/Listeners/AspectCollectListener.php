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

namespace Max\Di\Listeners;

use Max\Di\Annotations\MethodAnnotation;
use Max\Di\AspectManager;
use Max\Di\Contracts\AspectInterface;
use Max\Event\Contracts\EventListenerInterface;

class AspectCollectListener implements EventListenerInterface
{
    /**
     * @return iterable
     */
    public function listen(): iterable
    {
        return [];
    }

    /**
     * @param object $event
     */
    public function process(object $event): void
    {
        /** @var MethodAnnotation|AspectInterface $event */
        $reflectionClass  = $event->getReflectionClass();
        $reflectionMethod = $event->getReflectionMethod();
        AspectManager::addMethodAspect($reflectionClass->getName(), $reflectionMethod->getName(), $event);
    }
}
