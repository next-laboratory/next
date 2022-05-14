<?php

namespace Max\Cache\Tests;

use Max\Cache\Cache;
use Max\Container\Exceptions\NotFoundException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;
use function PHPUnit\Framework\assertEquals;

class CacheTest extends TestCase
{
    /**
     * @var CacheInterface|Cache
     */
    protected Cache|CacheInterface $cache;

    /**
     * @return void
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        $this->cache = (new Cache(require '../publish/cache.php'))->store();
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->cache->clear();
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     */
    public function testGet()
    {
        $this->cache->set(__METHOD__, __METHOD__);
        $this->assertEquals($this->cache->get(__METHOD__), __METHOD__);
    }

    /**
     * @return void
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
     * @return void
     * @throws InvalidArgumentException
     */
    public function testSet()
    {
        $this->cache->set(__METHOD__, __METHOD__);
        $this->assertEquals($this->cache->get(__METHOD__), __METHOD__);
    }


    /**
     * @return void
     * @throws InvalidArgumentException
     */
    public function testRemember()
    {
        $value = $this->cache->remember('value', function() {
            return 'value';
        }, 2);
        $this->assertEquals($value, 'value');
        $this->assertEquals($this->cache->get('value'), 'value');
        sleep(3);
        $this->assertEquals($this->cache->get('value'), null);
    }

    /**
     * @return void
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
     * @return void
     * @throws InvalidArgumentException
     */
    public function testHas()
    {
        $this->cache->set(__METHOD__, __METHOD__);
        $this->assertTrue($this->cache->has(__METHOD__));
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     */
    public function testSetMultiple()
    {
        $this->cache->setMultiple([__METHOD__ => __METHOD__, __FUNCTION__ => __FUNCTION__]);
        $this->assertEquals($this->cache->get(__METHOD__), __METHOD__);
        $this->assertEquals($this->cache->get(__FUNCTION__), __FUNCTION__);
    }

    /**
     * @return void
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
     * @return void
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
     * @return void
     * @throws InvalidArgumentException
     */
    public function testPull()
    {
        $this->cache->set(__METHOD__, __METHOD__);
        $this->assertEquals($this->cache->pull(__METHOD__), __METHOD__);
        $this->assertFalse($this->cache->has(__METHOD__));
    }

    /**
     * @return void
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
     * @return void
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
