<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\AsyncQueue;

use Max\AsyncQueue\Contract\DriverInterface;
use Max\AsyncQueue\Contract\JobInterface;
use Max\AsyncQueue\Event\TaskExecuted;
use Max\AsyncQueue\Exception\InvalidJobException;
use Max\AsyncQueue\Exception\ReleaseException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;
use function usleep;

class Queue
{
    public function __construct(
        protected DriverInterface           $driver,
        protected ?EventDispatcherInterface $eventDispatcher = null
    )
    {
    }

    /**
     * @param EventDispatcherInterface|null $eventDispatcher
     */
    public function setEventDispatcher(?EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function push(Job $job, int $delay = 0): void
    {
        $this->driver->push($job, $delay);
    }

    public function consume(int $retries = 3): void
    {
        while (true) {
            try {
                $job = $this->driver->pop();
                if (($leftTime = $job->leftTime()) > 0) {
                    usleep((int)($leftTime * 1000000));
                }
                $job->run();
                $this->eventDispatcher?->dispatch(new TaskExecuted());
            } catch (ReleaseException|InvalidJobException $e) {
                echo $e->getMessage();
                continue;
            } catch (Throwable $throwable) {
                if (!empty($job)) {
                    if ($job->getAttempts() >= $retries) {
                        $this->failedJob($job);
                        continue;
                    }
                    $this->driver->push($job, 3);
                }
                echo $throwable->getMessage() . PHP_EOL;
            }
        }
    }

    protected function failedJob(JobInterface $job): void
    {
        $this->driver->fail($job);
    }
}
