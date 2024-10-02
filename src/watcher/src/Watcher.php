<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Next\Watcher;

use Next\Watcher\Contract\DriverInterface;

class Watcher
{
    public function __construct(
        protected DriverInterface $driver,
    )
    {
    }

    public function run(): void
    {
        $this->writeLine('Watching filesystem');

        $this->driver->watch();
    }

    public function writeLine(string $message): void
    {
        printf("\033[33m [INFO] \033[0m %s\n", $message);
    }
}
