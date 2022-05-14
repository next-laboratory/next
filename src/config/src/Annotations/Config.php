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

namespace Max\Config\Annotations;

use Attribute;
use Max\Config\Repository;
use Max\Di\Context;
use Max\Aop\Contracts\PropertyAttribute;
use Max\Di\Exceptions\PropertyHandleException;
use Max\Di\ReflectionManager;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Config implements PropertyAttribute
{
    /**
     * @param string     $key     键
     * @param mixed|null $default 默认值
     */
    public function __construct(
        protected string $key,
        protected mixed  $default = null
    )
    {
    }

    /**
     * @param object $object
     * @param string $property
     *
     * @return void
     */
    public function handle(object $object, string $property): void
    {
        try {
            $container          = Context::getContainer();
            $reflectionProperty = ReflectionManager::reflectProperty($object::class, $property);
            $container->setValue($object, $reflectionProperty, $container->make(Repository::class)->get($this->key, $this->default));
        } catch (\Throwable $throwable) {
            throw new PropertyHandleException('Property assign failed. ' . $throwable->getMessage());
        }
    }
}
