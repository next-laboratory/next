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

use ReflectionClass;
use ReflectionMethod;

abstract class MethodAnnotation
{
    /**
     * @var ReflectionClass
     */
    protected ReflectionClass $reflectionClass;
    /**
     * @var ReflectionMethod
     */
    protected ReflectionMethod $reflectionMethod;

    /**
     * @param ReflectionClass  $reflectionClass
     * @param ReflectionMethod $reflectionMethod
     */
    public function setReflection(ReflectionClass $reflectionClass, ReflectionMethod $reflectionMethod)
    {
        $this->reflectionClass  = $reflectionClass;
        $this->reflectionMethod = $reflectionMethod;
    }

    /**
     * @return ReflectionClass
     */
    public function getReflectionClass(): ReflectionClass
    {
        return $this->reflectionClass;
    }

    /**
     * @return ReflectionMethod
     */
    public function getReflectionMethod(): ReflectionMethod
    {
        return $this->reflectionMethod;
    }
}
