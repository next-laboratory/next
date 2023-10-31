<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Utils\Tests;

use Next\Utils\BC;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

/**
 * @internal
 * @coversNothing
 */
class BCTest extends TestCase
{
    /**
     * @throws ExpectationFailedException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testNew()
    {
        $value = 1.23;
        $b     = BC::new($value, 2);
        $this->assertInstanceOf(BC::class, $b);
        $this->assertEquals($b->toString(), $value);
    }

    /**
     * @throws InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public function testAdd()
    {
        $b = BC::new(1.23);
        $b = $b->add(2.34, 2);
        $this->assertEquals(3.57, $b->toString());
    }
}
