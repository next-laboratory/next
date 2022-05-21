<?php

namespace Max\Workerman;

use Max\Config\Contracts\ConfigInterface;
use Max\Di\Container;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;
use Workerman\Worker;

class Server
{
    public const EVENT_ON_MESSAGE       = 'onMessage';
    public const EVENT_ON_CLOSE         = 'onClose';
    public const EVENT_ON_BUFFER_FULL   = 'onBufferFull';
    public const EVENT_ON_WORKER_START  = 'onWorkerStart';
    public const EVENT_ON_WORKER_RELOAD = 'onWorkerReload';
    public const EVENT_ON_CONNECT       = 'onConnect';
    public const EVENT_ON_BUFFER_DRAIN  = 'onBufferDrain';
    public const EVENT_ON_ERROR         = 'onError';
    protected array $config;
    protected array $workers = [];

    public function __construct(ConfigInterface $config, protected Container $container)
    {
        $this->config = $config->get('workerman');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function start(): void
    {
        foreach ($this->config['servers'] as $name => $config) {
            $serverConfig   = new ServerConfig($config);
            $workers[$name] = $worker = new Worker($serverConfig->getListen());
            foreach ($serverConfig->getSettings() as $key => $value) {
                $worker->{$key} = $value;
            }
            foreach ($serverConfig->getCallbacks() as $event => $callback) {
                [$class, $method] = $callback;
                $worker->{$event} = [$this->container->make($class), $method];
            }
        }

        Worker::runAll();
    }

    public function getWorker(string $name): mixed
    {
        return $this->workers[$name] ?? null;
    }
}
