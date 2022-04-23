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

namespace Max\Di\Annotation;

use Attribute;
use Max\Di\Context;
use Max\Di\Contracts\PropertyAttribute;
use Max\Di\Exceptions\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Inject implements PropertyAttribute
{
    /**
     * @param string|null $id 注入的类型
     */
    public function __construct(protected ?string $id = null)
    {
    }

    /**
     * @param ReflectionClass   $reflectionClass
     * @param ReflectionProperty $reflectionProperty
     * @param object             $object
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function handle(ReflectionClass $reflectionClass, ReflectionProperty $reflectionProperty, object $object): void
    {
        $container = Context::getContainer();
        if ((!is_null($type = $reflectionProperty->getType()) && $type = $type->getName()) || $type = $this->id) {
            $container->setValue($object, $reflectionProperty, $container->make($type));
        }
    }
}
