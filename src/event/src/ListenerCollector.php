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

namespace Max\Event;

use Max\Di\Annotation\Collector\AbstractCollector;
use Max\Event\Annotations\Listen;

class ListenerCollector extends AbstractCollector
{
    /**
     * @var array
     */
    protected static array $listeners = [];

    /**
     * @param string $class
     * @param object $attribute
     *
     * @return void
     */
    public static function collectClass(string $class, object $attribute): void
    {
        if ($attribute instanceof Listen && !in_array($class, self::$listeners)) {
            self::$listeners[] = $class;
        }
    }

    /**
     * @return array
     */
    public static function getListeners(): array
    {
        return self::$listeners;
    }
}
