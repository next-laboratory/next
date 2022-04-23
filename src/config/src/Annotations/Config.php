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
use Max\Di\Contracts\PropertyAttribute;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionProperty;

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
     * @param ReflectionClass    $reflectionClass
     * @param ReflectionProperty $reflectionProperty
     * @param object             $object
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function handle(ReflectionClass $reflectionClass, ReflectionProperty $reflectionProperty, object $object): void
    {
        $container = Context::getContainer();
        $container->setValue(
            $object,
            $reflectionProperty,
            $container->get(Repository::class)->get($this->key, $this->default)
        );
    }
}
