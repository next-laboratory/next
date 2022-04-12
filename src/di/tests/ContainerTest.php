<?php

namespace Max\Di;

use Max\Di\Tests\Concerns\TestCall;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ContainerTest extends TestCase
{

    public function testMake()
    {

    }

    public function testOffsetGet()
    {

    }

    public function testHas()
    {

    }

    public function testSet()
    {

    }

    public function testResolve()
    {

    }

    public function test__isset()
    {

    }

    public function testGetAlias()
    {

    }

    public function testOffsetSet()
    {

    }

    public function testOffsetUnset()
    {

    }

    public function testOffsetExists()
    {

    }

    public function test__construct()
    {
        $this->assertInstanceOf(ContainerInterface::class, new Container());
    }

    public function testCall()
    {
        call([TestCall::class, 'noneParams']);
    }

    public function testCallFunc()
    {

    }

    public function testGet()
    {

    }

    public function testUnAlias()
    {

    }

    public function test__get()
    {

    }

    public function testAlias()
    {

    }

    public function testHasAlias()
    {

    }

    public function testRemove()
    {

    }
}
