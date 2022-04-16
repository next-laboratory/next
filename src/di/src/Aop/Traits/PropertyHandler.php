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

namespace Max\Di\Aop\Traits;

use Max\Di\AnnotationManager;
use Max\Di\Context;
use Max\Di\Contracts\PropertyAttribute;
use Max\Di\Exceptions\ContainerException;
use Max\Di\ReflectionManager;
use ReflectionException;
use Throwable;

trait PropertyHandler
{
    /**
     * @throws ReflectionException
     */
    protected function __handleProperties()
    {
        $reflectionClass = ReflectionManager::reflectClass(static::class);
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            try {
                foreach (AnnotationManager::getPropertyAnnotations($reflectionClass->getName(), $reflectionProperty->getName()) as $attribute) {
                    if ($attribute instanceof PropertyAttribute) {
                        $attribute->handle($reflectionClass, $reflectionProperty, $this);
                    }
                }
            } catch (Throwable $throwable) {
                throw new ContainerException(
                    sprintf('Cannot inject Property into %s. (%s)',
                        $reflectionClass->getName(), $throwable->getMessage()
                    )
                );
            }
        }
    }
}
