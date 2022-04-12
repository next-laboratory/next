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

use Max\Queue\Contracts\QueueHandlerInterface;

abstract class Job
{
    /**
     * @var int
     */
    public int $attempts = 0;

    /**
     * @var QueueHandlerInterface
     */
    protected QueueHandlerInterface $queueHandler;

    /**
     * @return mixed
     */
    public function attempts()
    {
        return $this->attempts();
    }

    /**
     *
     */
    public function release()
    {

    }

    /**
     * @param QueueHandlerInterface $queueHandler
     */
    public function setQueueHandler(QueueHandlerInterface $queueHandler)
    {
        $this->queueHandler = $queueHandler;
    }
}
