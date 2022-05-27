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

namespace Max\Aop\Collectors;

use Max\Aop\Contracts\CollectorInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

abstract class AbstractCollector implements CollectorInterface
{
    public static function collectClass(ReflectionClass $reflectionClass, object $attribute): void
    {
    }

    public static function collectMethod(ReflectionMethod $reflectionMethod, object $attribute): void
    {
    }

    public static function collectProperty(ReflectionProperty $reflectionProperty, object $attribute): void
    {
    }
}
