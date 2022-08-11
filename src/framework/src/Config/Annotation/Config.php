<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Config\Annotation;

use Attribute;
use Max\Aop\Contract\PropertyAnnotation;
use Max\Aop\Exception\PropertyHandleException;
use Max\Config\Contract\ConfigInterface;
use Max\Di\Context;
use Max\Di\Reflection;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Config implements PropertyAnnotation
{
    /**
     * @param string     $key     键
     * @param null|mixed $default 默认值
     */
    public function __construct(
        protected string $key,
        protected mixed $default = null
    ) {
    }

    public function handle(object $object, string $property): void
    {
        try {
            $container          = Context::getContainer();
            $reflectionProperty = Reflection::property($object::class, $property);
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($object, $container->make(ConfigInterface::class)->get($this->key, $this->default));
        } catch (\Throwable $throwable) {
            throw new PropertyHandleException('Property assign failed. ' . $throwable->getMessage());
        }
    }
}
