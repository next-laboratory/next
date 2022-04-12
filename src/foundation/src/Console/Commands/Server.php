<?php
declare(strict_types=1);

namespace Max\Foundation\Console\Commands;

use Max\Console\Commands\Command;
use Max\Di\Annotations\Inject;
use Max\Di\Exceptions\NotFoundException;
use Max\Server\Server as MaxSwooleServer;
use Psr\Container\ContainerExceptionInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use ReflectionException;

class Server extends Command
{
    /**
     * @var string
     */
    protected string $name = 'server';

    /**
     * @var string
     */
    protected string $description = 'Start swoole server.';

    #[Inject]
    protected EventDispatcherInterface $eventDispatcher;

    /**
     * @return void
     * @throws NotFoundException
     * @throws ReflectionException|ContainerExceptionInterface
     */
    public function run()
    {
        switch ($this->input->getOption('-a')) {
            case 'start':
                $server = new MaxSwooleServer(config('server'));
                $server->start();
            case 'stop':
                posix_kill((int)file_get_contents('/var/run/max-php-manager.pid'), SIGTERM);
                posix_kill((int)file_get_contents('/var/run/max-php-master.pid'), SIGTERM);
        }
    }
}
