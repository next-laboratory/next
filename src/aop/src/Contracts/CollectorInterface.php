<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Aop\Contracts;

interface CollectorInterface
{
    public static function collectClass(string $class, object $attribute): void;

    public static function collectMethod(string $class, string $method, object $attribute): void;

    public static function collectProperty(string $class, string $property, object $attribute): void;

    public static function collectorMethodParameter(string $class, string $method, string $parameter, object $attribute);
}
