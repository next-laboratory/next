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

abstract class AnnotationCollector implements CollectorInterface
{
    /**
     * 收集类注解
     */
    public static function collectClass(string $class, object $attribute): void
    {
    }

    /**
     * 收集类方法注解
     */
    public static function collectMethod(string $class, string $method, object $attribute): void
    {
    }

    /**
     * 收集属性注解
     */
    public static function collectProperty(string $class, string $property, object $attribute): void
    {
    }

    /**
     * 缓存
     */
    public static function export(string $dir): void
    {
    }

    /**
     * 从缓存恢复
     */
    public static function import(string $dir): void
    {
    }
}
