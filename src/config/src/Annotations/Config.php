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
use Max\Di\Contracts\PropertyAttribute;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Config implements PropertyAttribute
{
    /**
     * @param string     $key     键
     * @param mixed|null $default 默认值
     */
    public function __construct(protected string $key, protected mixed $default = null)
    {
    }

    /**
     * @param ContainerInterface $container
     * @param ReflectionProperty $property
     * @param object             $object
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function handle(ContainerInterface $container, ReflectionProperty $property, object $object)
    {
        $container->setValue($object, $property, $container->get(Repository::class)->get($this->key, $this->default));
    }
}
