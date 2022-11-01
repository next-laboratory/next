<?php

namespace Max\AsyncQueue\Driver;

use Max\AsyncQueue\Contract\DriverInterface;
use Max\AsyncQueue\Contract\JobInterface;
use Max\AsyncQueue\Contract\PackerInterface;
use Max\AsyncQueue\Exception\InvalidJobException;
use Max\Redis\Redis;
use RedisException;

class RedisDriver implements DriverInterface
{
    protected const QUEUE  = 'maxphp:queue:%s';
    protected const FAILED = 'maxphp:queue:failed';

    public function __construct(
        protected Redis           $redis,
        protected PackerInterface $packer,
    )
    {
    }

    /**
     * @throws RedisException
     */
    public function push(JobInterface $job, int $delay = 0): bool
    {
        $job->setHandleTime(time() + $delay);
        $data = $this->packer->pack($job);
        return (bool)$this->redis->lPush(self::QUEUE, $data);
    }

    public function delete(JobInterface $job): bool
    {
        // TODO: Implement delete() method.
    }

    public function pop(): JobInterface
    {
        try {
            POP:
            if ($data = $this->redis->brPop(self::QUEUE, 0)) {
                $job = unserialize($data[1]);
                if (!$job instanceof JobInterface) {
                    throw new InvalidJobException('Job 类型不正确');
                }
                return $job;
            }
            throw new RedisException('dequeue failed');
        } catch (RedisException $e) {
            dump($e);
            goto POP;
        }
    }

    public function ack(mixed $data): bool
    {
        // TODO: Implement ack() method.
    }

    public function fail(mixed $data): bool
    {
        // TODO: Implement fail() method.
    }

    public function consume(): void
    {
        // TODO: Implement consume() method.
    }

    public function reload(string $queue = null): int
    {
        // TODO: Implement reload() method.
    }

    public function flush(string $queue = null): bool
    {
        // TODO: Implement flush() method.
    }

    public function info(): array
    {
        // TODO: Implement info() method.
    }
}
