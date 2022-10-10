<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Watcher;

use Max\Watcher\Contract\DriverInterface;

class Watcher
{
    public function __construct(
        protected DriverInterface $driver,
    ) {
    }

    public function run(): void
    {
        echo 'Watching changed files.' . PHP_EOL;

        $this->driver->watch();
    }
}
