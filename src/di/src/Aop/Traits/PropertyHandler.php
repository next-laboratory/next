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
use Max\Di\Contracts\PropertyAttribute;
use Max\Di\Exceptions\ContainerException;
use Max\Di\ReflectionManager;
use Throwable;

trait PropertyHandler
{
    /**
     * @return void
     */
    protected function __handleProperties(): void
    {
        $class = static::class;
        foreach (AnnotationManager::getPropertiesAnnotations($class) as $property => $attributes) {
            try {
                foreach ($attributes as $attribute) {
                    if ($attribute instanceof PropertyAttribute) {
                        $attribute->handle(
                            ReflectionManager::reflectClass($class), ReflectionManager::reflectProperty($class, $property), $this
                        );
                    }
                }
            } catch (Throwable $throwable) {
                throw new ContainerException(
                    sprintf('Cannot inject Property %s into %s. (%s)',
                        $property, $class, $throwable->getMessage()
                    )
                );
            }
        }
    }
}
