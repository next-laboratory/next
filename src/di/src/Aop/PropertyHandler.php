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

namespace Max\Di\Aop;

use Max\Di\Annotation\Collector\PropertyAttributeCollector;
use Max\Di\Exceptions\PropertyHandleException;
use Max\Di\ReflectionManager;
use Throwable;

/**
 * 用于处理实例化属性的Trait
 * 该Trait会被切入的类引入，并且在构造方法中调用下列方法来处理对象的属性
 * 能够用于属性的注解必须实现\Max\Di\Contracts\PropertyAttribute接口
 * 适用于容器实例化的类或者new关键字实例化的类
 */
trait PropertyHandler
{
    /**
     * @return void
     */
    protected function __handleProperties(): void
    {
        $class = static::class;
        foreach (PropertyAttributeCollector::getClassPropertyAttributes($class) as $property => $attributes) {
            try {
                foreach ($attributes as $attribute) {
                    $attribute->handle(
                        ReflectionManager::reflectClass($class), ReflectionManager::reflectProperty($class, $property), $this
                    );
                }
            } catch (Throwable $throwable) {
                throw new PropertyHandleException('Property handle failed. ' . $throwable->getMessage());
            }
        }
    }
}
