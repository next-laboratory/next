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
use Max\Di\ReflectionManager;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;

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
     * @param object $object
     * @param string $property
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function handle(object $object, string $property): void
    {
        $container          = Context::getContainer();
        $reflectionProperty = ReflectionManager::reflectProperty($object::class, $property);
        if ((!is_null($type = $reflectionProperty->getType()) && $type = $type->getName()) || $type = $this->id) {
            $container->setValue($object, $reflectionProperty, $container->make($type));
        }
    }
}
