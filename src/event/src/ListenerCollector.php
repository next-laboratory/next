<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Event;

use Max\Aop\Collector\AbstractCollector;
use Max\Event\Attribute\Listen;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;

class ListenerCollector extends AbstractCollector
{
    /**
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     */
    public static function collectClass(string $class, object $attribute): void
    {
        if ($attribute instanceof Listen) {
            make(ListenerProvider::class)->addListener(make($class));
        }
    }
}
