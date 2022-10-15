<?php

namespace Max\Bc\Tests;

use Max\Bc\B;
use PHPUnit\Framework\TestCase;

class BTest extends TestCase
{
    public function testNew()
    {
        $value = 1.23;
        $b     = B::new($value, 2);
        $this->assertInstanceOf(B::class, $b);
        $this->assertEquals($b->toString(), $value);
    }

    public function testAdd()
    {
        $b = B::new(1.23);
        $b = $b->add(2.34, 2);
        $this->assertEquals($b->toString(), 3.57);
    }
}