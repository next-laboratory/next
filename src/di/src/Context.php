<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Di;

use Psr\Container\ContainerInterface;

class Context
{
    protected static ContainerInterface $container;

    public static function hasContainer(): bool
    {
        return isset(self::$container);
    }

    public static function getContainer(): ContainerInterface
    {
        if (! self::hasContainer()) {
            self::$container = new Container();
            self::$container->set(ContainerInterface::class, self::$container);
            self::$container->set(Container::class, self::$container);
        }
        return self::$container;
    }

    public static function setContainer(ContainerInterface $container): void
    {
        self::$container = $container;
    }
}
