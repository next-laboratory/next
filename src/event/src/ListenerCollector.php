<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Event;

use Max\Aop\Collectors\AbstractCollector;
use Max\Event\Annotations\Listen;

class ListenerCollector extends AbstractCollector
{
    protected static array $listeners = [];

    public static function collectClass(string $class, object $attribute): void
    {
        if ($attribute instanceof Listen && ! in_array($class, self::$listeners)) {
            self::$listeners[] = $class;
        }
    }

    public static function getListeners(): array
    {
        return self::$listeners;
    }
}
