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

namespace Max\Swoole;

use Max\Swoole\Events\OnFinish;
use Max\Swoole\Events\OnTask;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Constant;
use Swoole\Server;
use Swoole\Server\Task;

class Callbacks
{
    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(protected EventDispatcherInterface $eventDispatcher)
    {
    }

    /**
     * @return mixed
     */
    public function onTask(): mixed
    {
        $args   = func_get_args();
        $server = $args[0];
        if (isset($server->setting[Constant::OPTION_TASK_ENABLE_COROUTINE]) && $server->setting[Constant::OPTION_TASK_ENABLE_COROUTINE]) {
            return (function(Server $server, Task $task): mixed {
                return $this->eventDispatcher->dispatch(new OnTask($server, $task->id, $task->worker_id, $task->data))->data;
            })(...$args);
        }
        return (function(Server $server, int $taskId, int $workerId, mixed $data): mixed {
            return $this->eventDispatcher->dispatch(new OnTask($server, $taskId, $workerId, $data))->data;
        })(...$args);
    }

    /**
     * @param Server $server
     * @param int    $taskId
     * @param mixed  $data
     */
    public function onFinish(Server $server, int $taskId, mixed $data): void
    {
        $this->eventDispatcher->dispatch(new OnFinish($server, $taskId, $data));
    }
}
