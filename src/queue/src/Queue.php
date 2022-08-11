<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Queue;

use Max\Config\Contract\ConfigInterface;
use Max\Queue\Contracts\QueueHandlerInterface;
use Max\Queue\Exceptions\InvalidJobException;
use Max\Queue\Jobs\DelayedJob;
use Max\Queue\Jobs\Job;
use Throwable;

use function count;
use function is_array;
use function is_string;
use function microtime;
use function serialize;
use function sleep;
use function unserialize;
use function usleep;

class Queue
{
    protected array $config;

    protected QueueHandlerInterface $handler;

    public function __construct(ConfigInterface $config)
    {
        $this->config  = $config->get('queue');
        $this->handler = $this->makeQueue();
    }

    /**
     * @param array|object|string $job
     */
    public function push($job, string $queue = 'default'): void
    {
        $this->handler->enqueue($queue, serialize($job));
    }

    /**
     * @param float $delay 延时时长/秒
     */
    public function later(DelayedJob $delayJob, float $delay = 15): void
    {
        $delayJob->handleTime = microtime(true) + $delay;
        $this->handler->enqueue('delay', serialize($delayJob));
    }

    public function work(?string $queue): void
    {
        while (true) {
            try {
                if ($job = $this->dequeueJob($queue ?? 'default')) {
                    $this->handleJob($job);
                    echo 'Task Executed Successfully' . PHP_EOL;
                }
            } catch (Throwable $throwable) {
                echo $throwable->getMessage() . PHP_EOL;
                sleep($this->config['sleep']);
            }
        }
    }

    /**
     * @return mixed
     */
    protected function makeQueue()
    {
        $queue  = $this->config['default'];
        $config = $this->config['connections'][$queue];
        $queue  = $config['driver'];
        $config = $config['config'];
        return new $queue($config);
    }

    /**
     * @param $queue
     *
     * @throws InvalidJobException
     * @return false|Job
     */
    protected function dequeueJob($queue): Job|bool
    {
        if ($job = $this->handler->dequeue($queue)) {
            $job    = unserialize($job);
            $params = [];
            if (is_array($job) && count($job) === 2) {
                [$job, $params] = $job;
            }
            if (is_string($job)) {
                $job = new $job(...(array) $params);
            }
            if ($job instanceof Job) {
                return $job;
            }
            throw new InvalidJobException('Task classes must implement the JobInterface interface.');
        }
        return false;
    }

    protected function handleJob(Job $job): void
    {
        try {
            if ($job instanceof DelayedJob && ($leftTime = $job->leftTime()) > 0) {
                usleep((int) ($leftTime * 1e6));
            }
            $job->handle();
        } catch (Throwable $throwable) {
            // 记录日志，重新入队
        }
    }
}
