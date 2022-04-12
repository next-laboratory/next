<?php

declare(strict_types=1);

/**
 * This file is part of the Max package.
 *
 * (c) Cheng Yao <987861463@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Max\Queue\Jobs;

use function microtime;

abstract class DelayedJob extends Job
{
    /**
     * @var float
     */
    public float $handleTime;

    /**
     * @return float
     */
    public function leftTime(): float
    {
        return $this->handleTime - microtime(true);
    }
}
