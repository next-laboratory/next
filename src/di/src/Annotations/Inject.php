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

use Attribute;
use Max\Di\Contracts\PropertyAttribute;
use Max\Di\Exceptions\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
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
     * @param ContainerInterface $container
     * @param ReflectionProperty $property
     * @param object             $object
     *
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function handle(ContainerInterface $container, ReflectionProperty $property, object $object)
    {
        if ((!is_null($type = $property->getType()) && $type = $type->getName()) || $type = $this->id) {
            $container->setValue($object, $property, $container->make($type));
        }
    }
}
