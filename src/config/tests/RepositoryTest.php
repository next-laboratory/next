<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Config\Tests;

use Max\Config\Repository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class RepositoryTest extends TestCase
{
    protected Repository $repository;

    protected function setUp(): void
    {
        $this->repository = new Repository([
            'app' => [
                'debug' => true,
                'name' => 'maxphp',
            ],
            'cache' => [
                'driver' => 'file',
            ],
        ]);
    }

    public function testGet()
    {
        $this->assertTrue($this->repository->get('app.debug'));
        $this->assertEquals(['driver' => 'file'], $this->repository->get('cache'));
    }

    public function testSet()
    {
        $this->repository->set('cookie.name', 'maxphp');
        $this->assertEquals('maxphp', $this->repository->get('cookie.name'));
    }
}
