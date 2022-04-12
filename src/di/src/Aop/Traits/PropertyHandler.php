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
        $reflectionClass = ReflectionManager::reflectClass($this::class);
        foreach ($reflectionClass->getProperties() as $property) {
            try {
                foreach ($property->getAttributes() as $attribute) {
                    $instance = $attribute->newInstance();
                    if (!$instance instanceof PropertyAttribute) {
                        throw new ContainerException('Attribute ' . $instance::class . ' must implements PropertyAttribute interface.');
                    }
                    $instance->handle(Context::getContainer(), $property, $this);
                }
            } catch (Throwable $throwable) {
                throw new ContainerException(
                    sprintf('Cannot inject Property %s into %s. (%s)',
                        $property->getName(), $reflectionClass->getName(), $throwable->getMessage()
                    )
                );
            }
        }
    }
}
