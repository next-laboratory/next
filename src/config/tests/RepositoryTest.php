<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Config\Tests;

use Next\Config\Repository;
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
                'name'  => 'nextphp',
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
        $this->repository->set('cookie.name', 'nextphp');
        $this->assertEquals('nextphp', $this->repository->get('cookie.name'));
    }
}
