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

namespace Max\Di;

use Psr\Container\ContainerInterface;

class Context
{
    /**
     * @var ContainerInterface
     */
    protected static ContainerInterface $container;

    /**
     * @return bool
     */
    public static function hasContainer(): bool
    {
        return isset(self::$container);
    }

    /**
     * @return ContainerInterface
     */
    public static function getContainer(): ContainerInterface
    {
        if (!self::hasContainer()) {
            self::$container = new Container();
            self::$container->set(ContainerInterface::class, self::$container);
            self::$container->set(Container::class, self::$container);
        }
        return self::$container;
    }

    /**
     * @param ContainerInterface $container
     */
    public static function setContainer(ContainerInterface $container): void
    {
        self::$container = $container;
    }
}
