<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Cache\Tests;

use Next\Cache\Cache;
use Next\Cache\CacheException;
use Next\Cache\Driver\FileDriver;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * @internal
 * @coversNothing
 */
class CacheTest extends TestCase
{
    protected CacheInterface $fileCache;

    /**
     * @throws CacheException
     */
    protected function setUp(): void
    {
        $tmpDir          = sys_get_temp_dir();
        $this->fileCache = new Cache(new FileDriver($tmpDir . '/cache'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testSet()
    {
        $this->assertTrue($this->fileCache->set('foo', 'bar'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testHas()
    {
        $this->assertTrue($this->fileCache->has('foo'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testGet()
    {
        $this->assertEquals('bar', $this->fileCache->get('foo'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testGetMultiple()
    {
        $multiValues = $this->fileCache->getMultiple(['foo']);
        $this->assertArrayHasKey('foo', $multiValues);
        $this->assertEquals('bar', $multiValues['foo']);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testDelete()
    {
        $this->fileCache->delete('foo');
        $this->assertFalse($this->fileCache->has('foo'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testGetDefault()
    {
        $this->assertEquals('bio', $this->fileCache->get('foo', 'bio'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRemember()
    {
        $this->fileCache->remember('bar', function () {
            return 'foo';
        });
        $this->assertEquals('foo', $this->fileCache->get('bar'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testIncrement()
    {
        $this->fileCache->delete('count');
        $this->fileCache->increment('count');
        $this->assertEquals(1, $this->fileCache->get('count'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testDecrement()
    {
        $this->fileCache->decrement('count');
        $this->assertEquals(0, $this->fileCache->get('count'));
    }
}
