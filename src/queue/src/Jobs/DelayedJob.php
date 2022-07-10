<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Queue\Jobs;

use function microtime;

abstract class DelayedJob extends Job
{
    public float $handleTime;

    public function leftTime(): float
    {
        return $this->handleTime - microtime(true);
    }
}
