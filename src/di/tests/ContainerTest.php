<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Di\Tests;

use Next\Di\Context;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class ContainerTest extends TestCase
{
    protected ContainerInterface $container;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->container = Context::getContainer();
    }

    public function testBind()
    {
        $this->container->bind(FooInterface::class, Foo::class);
        $this->assertEquals($this->container->getBinding(FooInterface::class), Foo::class);
    }
}
