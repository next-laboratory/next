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

namespace Max\Di\Contracts;

interface CollectorInterface
{
    /**
     * @param string $class
     * @param object $attribute
     *
     * @return void
     */
    public static function collectClass(string $class, object $attribute): void;

    /**
     * @param string $class
     * @param string $method
     * @param object $attribute
     *
     * @return void
     */
    public static function collectMethod(string $class, string $method, object $attribute): void;

    /**
     * @param string $class
     * @param string $property
     * @param object $attribute
     *
     * @return void
     */
    public static function collectProperty(string $class, string $property, object $attribute): void;

    /**
     * 缓存
     *
     * @param string $dir
     *
     * @return void
     */
    public static function export(string $dir): void;

    /**
     * 恢复缓存
     *
     * @param string $dir
     *
     * @return void
     */
    public static function import(string $dir): void;
}
