<?php

namespace Max\Di\Tests;

use Max\Di\Context;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

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
