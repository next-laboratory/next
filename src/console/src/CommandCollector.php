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

namespace Max\Console;

use Max\Console\Annotations\Command;
use Max\Aop\Annotation\Collector\AbstractCollector;

class CommandCollector extends AbstractCollector
{
    /**
     * @var array
     */
    protected static array $container = [];

    /**
     * @param string $class
     * @param object $attribute
     *
     * @return void
     */
    public static function collectClass(string $class, object $attribute): void
    {
        if ($attribute instanceof Command) {
            self::add($class);
        }
    }

    /**
     * @param string $class
     *
     * @return void
     */
    public static function add(string $class): void
    {
        if (!in_array($class, self::$container)) {
            self::$container[] = $class;
        }
    }

    /**
     * @return array
     */
    public static function all(): array
    {
        return self::$container;
    }
}
