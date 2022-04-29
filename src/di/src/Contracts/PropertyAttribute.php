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

use ReflectionClass;
use ReflectionProperty;

interface PropertyAttribute
{
    /**
     * @param object $object   对象
     * @param string $property 属性
     *
     * @return void
     */
    public function handle(object $object, string $property): void;
}
