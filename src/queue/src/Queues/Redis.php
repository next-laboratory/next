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

namespace Max\Queue\Queues;

use Max\Queue\Contracts\QueueHandlerInterface;
use function array_replace;
use function sprintf;

class Redis implements QueueHandlerInterface
{
    /**
     * @var array
     */
    protected array $config = [
        'host' => '127.0.0.1',
        'port' => 6379,
    ];

    protected const QUEUE = 'maxphp:queue:%s';

    /**
     * @var \Redis
     */
    protected \Redis $redis;

    /**
     * @param array $config
     */
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
     * @param        $job
     * @param string $queue
     */
    public function enqueue(string $queue, $job)
    {
        $this->redis->lPush(sprintf(self::QUEUE, $queue), $job);
    }

    /**
     * @param string $queue
     *
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
     * 停止队列
     */
    public function stop()
    {
        $this->redis->close();
    }
}
