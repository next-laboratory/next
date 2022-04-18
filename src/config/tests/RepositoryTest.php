<?php

namespace Max\Config\Tests;

use Max\Config\Repository;
use PHPUnit\Framework\TestCase;

class RepositoryTest extends TestCase
{
    /**
     * @var Repository
     */
    protected Repository $repository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->tearDown();
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->repository = new Repository();
    }

    /**
     * @return void
     */
    public function testAll()
    {
        $this->assertEquals($this->repository->all(), []);
    }

    /**
     * @return void
     */
    public function testLoadOne()
    {
        $this->repository->loadOne('./examples/app.php');
        $this->assertArrayHasKey('app', $this->repository->all());
    }

    /**
     * @return void
     */
    public function testLoad()
    {
        $this->repository->load(['./examples/app.php', './examples/db.php']);
        $this->assertArrayHasKey('app', $this->repository->all());
        $this->assertArrayHasKey('db', $this->repository->all());
    }

    /**
     * @return void
     */
    public function testGet()
    {
        $this->repository->loadOne('./examples/app.php');
        $this->assertEquals($this->repository->get('app.id'), 123);
        $this->assertNull($this->repository->get('app.none'));
    }

    /**
     * @return void
     */
    public function testSet()
    {
        $this->repository->set(__METHOD__, __METHOD__);
        $this->assertEquals($this->repository->get(__METHOD__), __METHOD__);
    }
}
