<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Queue\Queue;

use Max\Queue\Contract\QueueHandlerInterface;

use function array_replace;
use function sprintf;

class Redis implements QueueHandlerInterface
{
    protected const QUEUE = 'maxphp:queue:%s';

    protected array $config = [
        'host' => '127.0.0.1',
        'port' => 6379,
    ];

    protected \Redis $redis;

    public function __construct(array $config)
    {
        $this->config = array_replace($this->config, $config);
        $this->redis  = new \Redis();
        $connect      = 'cli' === PHP_SAPI ? 'pconnect' : 'connect';
        $this->redis->{$connect}($this->config['host'], $this->config['port']);
        if ($password = $this->config['pass']) {
            $this->redis->auth($password);
        }
        $this->redis->select($this->config['database']);
    }

    /**
     * @param $job
     */
    public function enqueue(string $queue, $job)
    {
        $this->redis->lPush(sprintf(self::QUEUE, $queue), $job);
    }

    /**
     * @return bool|mixed
     */
    public function dequeue(string $queue)
    {
        if ($job = $this->redis->brPop(sprintf(self::QUEUE, $queue), 10)) {
            return $job[1];
        }
        return false;
    }

    /**
     * 停止队列.
     */
    public function stop()
    {
        $this->redis->close();
    }
}
