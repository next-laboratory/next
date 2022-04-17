<?php

declare(strict_types=1);

namespace Max\Server\Listeners;

use Max\Event\Contracts\EventListenerInterface;
use Max\Server\Events\OnManagerStart;
use Max\Server\Events\OnStart;
use Max\Server\Events\OnWorkerStart;

class ServerListener implements EventListenerInterface
{
    public const EVENT_START         = 'start';
    public const EVENT_WORKER_START  = 'workerStart';
    public const EVENT_MANAGER_START = 'managerStart';
    public const EVENT_TASK          = 'task';
    public const EVENT_CLOSE         = 'close';
    public const EVENT_FINISH        = 'finish';
    public const EVENT_MESSAGE       = 'message';
    public const EVENT_OPEN          = 'open';
    public const EVENT_REQUEST       = 'request';
    public const EVENT_RECEIVE       = 'receive';

    /**
     * @return iterable
     */
    public function listen(): iterable
    {
        return [
            OnStart::class,
            OnManagerStart::class,
            OnWorkerStart::class,
        ];
    }

    /**
     * @param object $event
     *
     * @return void
     */
    public function process(object $event): void
    {
        switch (true) {
            case $event instanceof OnStart:
                swoole_set_process_name('max-php-master');
                file_put_contents('/var/run/max-php-master.pid', $event->server->getMasterPid());
                break;
            case $event instanceof OnManagerStart:
                swoole_set_process_name('max-php-manager');
                file_put_contents('/var/run/max-php-manager.pid', $event->server->getManagerPid());
                break;
            case $event instanceof OnWorkerStart:
                $task = $event->server->taskworker ? 'task-' : '';
                swoole_set_process_name('max-php-' . $task . 'worker-' . $event->workerId);
        }
    }
}
