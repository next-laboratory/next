<?php

declare(strict_types=1);

namespace Max\Swoole\Listeners;

use Max\Console\Output\Formatter;
use Max\Event\Contracts\EventListenerInterface;
use Max\Swoole\Events\OnBeforeShutdown;
use Max\Swoole\Events\OnManagerStart;
use Max\Swoole\Events\OnManagerStop;
use Max\Swoole\Events\OnShutdown;
use Max\Swoole\Events\OnStart;
use Max\Swoole\Events\OnWorkerExit;
use Max\Swoole\Events\OnWorkerStart;
use Max\Swoole\Events\OnWorkerStop;

class ServerListener implements EventListenerInterface
{
    public const EVENT_START           = 'start';
    public const EVENT_WORKER_START    = 'workerStart';
    public const EVENT_MANAGER_START   = 'managerStart';
    public const EVENT_MANAGER_STOP    = 'managerStop';
    public const EVENT_WORKER_STOP     = 'workerStop';
    public const EVENT_WORKER_EXIT     = 'workerExit';
    public const EVENT_BEFORE_SHUTDOWN = 'beforeShutdown';
    public const EVENT_SHUTDOWN        = 'shutdown';
    public const EVENT_TASK            = 'task';
    public const EVENT_CLOSE           = 'close';
    public const EVENT_FINISH          = 'finish';
    public const EVENT_MESSAGE         = 'message';
    public const EVENT_OPEN            = 'open';
    public const EVENT_REQUEST         = 'request';
    public const EVENT_RECEIVE         = 'receive';

    /**
     * @return iterable
     */
    public function listen(): iterable
    {
        return [
            OnStart::class,
            OnManagerStart::class,
            OnWorkerStart::class,
            OnManagerStop::class,
            OnShutdown::class,
            OnBeforeShutdown::class,
            OnWorkerExit::class,
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
                break;
            case $event instanceof OnManagerStop:
                $pid = '/var/run/max-php-manager.pid';
                file_exists($pid) && unlink($pid);
                break;
            case $event instanceof OnWorkerExit:
                break;
            case $event instanceof OnShutdown:
                $pid = '/var/run/max-php-master.pid';
                file_exists($pid) && unlink($pid);
                echo (new Formatter())->setForeground('red')
                                      ->apply('Server stopped.') . PHP_EOL;
                break;
            case $event instanceof OnBeforeShutdown:
        }
    }
}
