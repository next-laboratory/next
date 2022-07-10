<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Queue\Jobs;

use Max\Queue\Contracts\QueueHandlerInterface;

abstract class Job
{
    public int $attempts = 0;

    protected QueueHandlerInterface $queueHandler;

    /**
     * @return mixed
     */
    public function attempts()
    {
        return $this->attempts();
    }

    public function release()
    {
    }

    public function setQueueHandler(QueueHandlerInterface $queueHandler)
    {
        $this->queueHandler = $queueHandler;
    }
}
