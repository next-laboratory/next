<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Cache\Tests;

use Max\Cache\Cache;
use Max\Di\Exceptions\NotFoundException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;

/**
 * @internal
 * @coversNothing
 */
class CacheTest extends TestCase
{
    protected Cache|CacheInterface $cache;

    /**
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        $this->cache = (new Cache(require '../publish/cache.php'))->store();
    }

    protected function tearDown(): void
    {
        $this->cache->clear();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testGet()
    {
        $this->cache->set(__METHOD__, __METHOD__);
        $this->assertEquals($this->cache->get(__METHOD__), __METHOD__);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testDelete()
    {
        $this->cache->set(__METHOD__, __METHOD__);
        $this->assertEquals($this->cache->get(__METHOD__), __METHOD__);
        $this->cache->delete(__METHOD__);
        $this->assertEquals($this->cache->get(__METHOD__), null);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testSet()
    {
        $this->cache->set(__METHOD__, __METHOD__);
        $this->assertEquals($this->cache->get(__METHOD__), __METHOD__);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRemember()
    {
        $value = $this->cache->remember('value', function () {
            return 'value';
        }, 2);
        $this->assertEquals($value, 'value');
        $this->assertEquals($this->cache->get('value'), 'value');
        sleep(3);
        $this->assertEquals($this->cache->get('value'), null);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testClear()
    {
        $this->cache->set(__METHOD__, __METHOD__);
        $this->cache->set(__FUNCTION__, __FUNCTION__);
        $this->cache->clear();
        $this->assertNull($this->cache->get(__METHOD__));
        $this->assertNull($this->cache->get(__FUNCTION__));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testHas()
    {
        $this->cache->set(__METHOD__, __METHOD__);
        $this->assertTrue($this->cache->has(__METHOD__));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testSetMultiple()
    {
        $this->cache->setMultiple([__METHOD__ => __METHOD__, __FUNCTION__ => __FUNCTION__]);
        $this->assertEquals($this->cache->get(__METHOD__), __METHOD__);
        $this->assertEquals($this->cache->get(__FUNCTION__), __FUNCTION__);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testGetMultiple()
    {
        $this->cache->set(__METHOD__, __METHOD__);
        $this->cache->set(__FUNCTION__, __FUNCTION__);
        $value = $this->cache->getMultiple([__METHOD__, __FUNCTION__]);
        $this->assertEquals($value, [__METHOD__ => __METHOD__, __FUNCTION__ => __FUNCTION__]);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testDeleteMultiple()
    {
        $this->cache->set(__METHOD__, __METHOD__);
        $this->cache->set(__FUNCTION__, __FUNCTION__);
        $this->cache->deleteMultiple([__METHOD__, __FUNCTION__]);
        $this->assertEquals($this->cache->getMultiple([__METHOD__, __FUNCTION__]), [__METHOD__ => null, __FUNCTION__ => null]);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testPull()
    {
        $this->cache->set(__METHOD__, __METHOD__);
        $this->assertEquals($this->cache->pull(__METHOD__), __METHOD__);
        $this->assertFalse($this->cache->has(__METHOD__));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testIncr()
    {
        $this->cache->set(__METHOD__, 1);
        $this->assertEquals($this->cache->get(__METHOD__), 1);
        $this->cache->incr(__METHOD__);
        $this->assertEquals($this->cache->get(__METHOD__), 2);
        $this->cache->incr(__METHOD__, 2);
        $this->assertEquals($this->cache->get(__METHOD__), 4);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testDecr()
    {
        $this->cache->set(__METHOD__, 3);
        $this->assertEquals($this->cache->get(__METHOD__), 3);
        $this->cache->decr(__METHOD__);
        $this->assertEquals($this->cache->get(__METHOD__), 2);
        $this->cache->decr(__METHOD__, 2);
        $this->assertEquals($this->cache->get(__METHOD__), 0);
    }
}
