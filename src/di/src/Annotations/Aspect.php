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

use Max\Di\AnnotationManager;
use Max\Di\Contracts\AspectInterface;
use Max\Di\Contracts\MethodAttribute;

abstract class Aspect implements AspectInterface, MethodAttribute
{
    /**
     * @var \ReflectionClass
     */
    protected \ReflectionClass $reflectionClass;

    /**
     * @var \ReflectionMethod
     */
    protected \ReflectionMethod $reflectionMethod;

    /**
     * @param \ReflectionClass  $reflectionClass
     * @param \ReflectionMethod $reflectionMethod
     *
     * @return void
     */
    public function handle(\ReflectionClass $reflectionClass, \ReflectionMethod $reflectionMethod)
    {
        $this->reflectionClass  = $reflectionClass;
        $this->reflectionMethod = $reflectionMethod;
        AnnotationManager::annotationMethod($reflectionClass->getName(), $reflectionMethod->getName(), $this);
    }

    /**
     * @return \ReflectionClass
     */
    public function getReflectionClass(): \ReflectionClass
    {
        return $this->reflectionClass;
    }

    /**
     * @return \ReflectionMethod
     */
    public function getReflectionMethod(): \ReflectionMethod
    {
        return $this->reflectionMethod;
    }
}