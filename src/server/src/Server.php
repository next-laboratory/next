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

use InvalidArgumentException;
use Max\Config\Contracts\ConfigInterface;
use Max\Di\Exceptions\NotFoundException;
use Max\Event\EventDispatcher;
use Max\Server\Events\OnBeforeShutdown;
use Max\Server\Events\OnManagerStart;
use Max\Server\Events\OnManagerStop;
use Max\Server\Events\OnShutdown;
use Max\Server\Events\OnStart;
use Max\Server\Events\OnWorkerStart;
use Max\Server\Events\OnWorkerStop;
use Max\Server\Listeners\ServerListener;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;
use RuntimeException;
use Swoole\Http\Server as SwooleHttpServer;
use Swoole\Process;
use Swoole\Server as SwooleServer;
use Swoole\WebSocket\Server as SwooleWebSocketServer;
use function array_replace_recursive;

class Server
{
    /**
     * HTTP服务
     */
    public const SERVER_HTTP = 1;

    /**
     * WebSocket服务
     */
    public const SERVER_WEBSOCKET = 2;

    /**
     * BASE
     */
    public const SERVER_BASE = 3;

    /**
     * 默认事件
     */
    protected const DEFAULT_EVENTS = [
        ServerListener::EVENT_START           => OnStart::class,
        ServerListener::EVENT_MANAGER_START   => OnManagerStart::class,
        ServerListener::EVENT_WORKER_START    => OnWorkerStart::class,
        ServerListener::EVENT_MANAGER_STOP    => OnManagerStop::class,
        ServerListener::EVENT_WORKER_STOP     => OnWorkerStop::class,
        ServerListener::EVENT_WORKER_EXIT     => OnWorkerExit::class,
        ServerListener::EVENT_BEFORE_SHUTDOWN => OnBeforeShutdown::class,
        ServerListener::EVENT_SHUTDOWN        => OnShutdown::class,
    ];

    /**
     * @var ?SwooleServer
     */
    protected ?SwooleServer $server = null;

    /**
     * @var array
     */
    protected array $config;

    /**
     * @param array                $config
     * @param EventDispatcher|null $eventDispatcher
     */
    public function __construct(ConfigInterface $config, protected ?EventDispatcher $eventDispatcher = null)
    {
        $this->config = $config->get('server');
        $servers      = $this->config['servers'];
        array_multisort(array_column($servers, 'type'), SORT_DESC, $servers);
        $this->eventDispatcher?->getListenerProvider()->addListener(new ServerListener());
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function start()
    {
        foreach ($this->config['servers'] as $config) {
            $config   = new ServerConfig($config);
            $name     = $config->getName();
            $host     = $config->getHost();
            $port     = $config->getPort();
            $sockType = $config->getSockType();
            $settings = array_replace_recursive($this->config['settings'], $config->getSettings());
            if (!$this->server instanceof SwooleServer) {
                $server = $this->server = $this->makeServer(
                    $config->getType(),
                    $host,
                    $port,
                    $this->config['mode'] ?? SWOOLE_PROCESS,
                    $sockType
                );
            } else {
                /* @var SwooleServer $server */
                $server = $this->server->addlistener($host, $port, $sockType);
            }
            $server->set($settings);
            foreach ($config->getCallbacks() as $event => $callback) {
                $server->on($event, [make($callback[0]), $callback[1]]);
            }
            echo 'Server "' . $name . '" listening at ' . $host . ':' . $port . PHP_EOL;
        }
        $this->server->start();
    }

    public function stop(): void
    {
        $pids = [
            '/var/run/max-php-manager.pid',
            '/var/run/max-php-master.pid',
        ];
        foreach ($pids as $pid) {
            if (!file_exists($pid)) {
                throw new RuntimeException('服务没有运行');
            }
            Process::kill((int)file_get_contents($pid), SIGTERM);
            unlink($pid);
        }
    }

    /**
     * 创建主Server
     *
     * @param int    $server
     * @param string $host
     * @param int    $port
     * @param int    $mode
     * @param int    $sockType
     *
     * @return SwooleServer
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws ReflectionException
     */
    protected function makeServer(int $server, string $host, int $port, int $mode, int $sockType): SwooleServer
    {
        $server = match ($server) {
            self::SERVER_HTTP => SwooleHttpServer::class,
            self::SERVER_WEBSOCKET => SwooleWebSocketServer::class,
            self::SERVER_BASE => SwooleServer::class,
            default => throw new InvalidArgumentException('Server type is invalid.'),
        };
        /** @var SwooleServer $server */
        $server = new $server($host, $port, $mode, $sockType);
        if (!is_null($this->eventDispatcher)) {
            foreach (self::DEFAULT_EVENTS as $key => $event) {
                $server->on($key, function() use ($event) {
                    $this->eventDispatcher->dispatch(new $event(...func_get_args()));
                });
            }
        }
        foreach ($this->config['callbacks'] ?? [] as $event => $callback) {
            $server->on($event, [make($callback[0]), $callback[1]]);
        }
        return $server;
    }

    /**
     * @param               $data
     * @param int           $workerId
     * @param callable|null $callback
     *
     * @return mixed
     */
    public function task($data, int $workerId = -1, ?callable $callback = null): mixed
    {
        return $this->getServer()->task($data, $workerId, $callback);
    }

    /**
     * @return SwooleServer|null
     */
    public function getServer(): SwooleServer|null
    {
        return $this->server;
    }
}
