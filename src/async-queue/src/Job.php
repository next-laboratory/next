<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\AsyncQueue;

use Max\AsyncQueue\Contract\JobInterface;
use Max\AsyncQueue\Exception\ReleaseException;
use Throwable;

abstract class Job implements JobInterface
{
    protected int $attempts   = 0;
    public int    $handleTime = 0;

    public function leftTime(): float
    {
        return $this->handleTime - time();
    }

    public function setHandleTime(int $handleTime)
    {
        $this->handleTime = $handleTime;
    }

    public function getAttempts(): int
    {
        return $this->attempts;
    }

    /**
     * @throws ReleaseException
     */
    public function release()
    {
        throw new ReleaseException();
    }

    final public function run(): void
    {
        try {
            $this->handle();
        } catch (Throwable $e) {
            $this->attempts++;
            throw $e;
        }
    }
}
