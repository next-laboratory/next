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

namespace Max\Di\Annotations;

use Max\Di\Context;
use Max\Di\Contracts\ClassAttribute;
use ReflectionClass;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Bind implements ClassAttribute
{
    /**
     * @param string $to
     */
    public function __construct(protected string $to)
    {
    }

    /**
     * @param ReflectionClass $reflectionClass
     *
     * @return void
     */
    public function handle(ReflectionClass $reflectionClass)
    {
        Context::getContainer()->alias($this->to, $reflectionClass->getName());
    }
}
