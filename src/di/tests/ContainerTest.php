<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Di\Tests;

use Max\Di\Context;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class ContainerTest extends TestCase
{
    protected ContainerInterface $container;

    protected function setUp(): void
    {
        $this->container = Context::getContainer();
    }

    public function testCallFunc()
    {
    }

    public function testBind()
    {
        $this->container->bind(FooInterface::class, Foo::class);
        $this->assertEquals($this->container->getBinding(FooInterface::class), Foo::class);
    }

    public function testCall()
    {
    }

    public function testBound()
    {
    }

    public function testHas()
    {
    }

    public function testGetBinding()
    {
    }

    public function testResolve()
    {
    }

    public function testSet()
    {
    }

    public function testGet()
    {
    }

    public function testRemove()
    {
    }

    public function testUnBind()
    {
    }

    public function testMake()
    {
    }
}
