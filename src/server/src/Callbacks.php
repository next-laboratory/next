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

namespace Max\Server;

use Max\Server\Events\OnFinish;
use Max\Server\Events\OnTask;
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
     * @param Server $server
     */
    public function start(Server $server)
    {
        swoole_set_process_name('max-php-master');
        file_put_contents('/var/run/max-php-master.pid', $server->getMasterPid());
    }

    /**
     * @param Server $server
     */
    public function managerStart(Server $server)
    {
        swoole_set_process_name('max-php-manager');
        file_put_contents('/var/run/max-php-manager.pid', $server->getManagerPid());
    }

    /**
     * @param Server $server
     * @param int    $workerId
     */
    public function workerStart(Server $server, int $workerId)
    {
        $task = '';
        if ($server->taskworker) {
            $task = 'task-';
        }
        swoole_set_process_name('max-php-' . $task . 'worker-' . $workerId);
    }

    /**
     * @return mixed
     */
    public function task(): mixed
    {
        $args   = func_get_args();
        $server = $args[0];
        if (isset($server->setting[Constant::OPTION_TASK_ENABLE_COROUTINE]) && $server->setting[Constant::OPTION_TASK_ENABLE_COROUTINE]) {
            return (function (Server $server, Task $task): mixed {
                return $this->eventDispatcher->dispatch(new OnTask($server, $task->id, $task->worker_id, $task->data))->data;
            })(...$args);
        }
        return (function (Server $server, int $taskId, int $workerId, mixed $data): mixed {
            return $this->eventDispatcher->dispatch(new OnTask($server, $taskId, $workerId, $data))->data;
        })(...$args);
    }

    /**
     * @param Server $server
     * @param int    $taskId
     * @param mixed  $data
     */
    public function finish(Server $server, int $taskId, mixed $data)
    {
        $this->eventDispatcher->dispatch(new OnFinish($server, $taskId, $data));
    }
}
