<?php

namespace Max\Di\Tests;

use Max\Di\Context;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ContextTest extends TestCase
{
    public function testHasContainer()
    {
        $this->assertFalse(Context::hasContainer());
    }

    public function testGetContainer()
    {
        $this->assertTrue(Context::getContainer() instanceof ContainerInterface);
    }

    public function testSetContainer()
    {
        Context::setContainer(new Container());
        $this->assertTrue(Context::getContainer() instanceof ContainerInterface);
    }

}
