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

use Max\Di\Exceptions\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;
use RuntimeException;
use Swoole\Http\Server as SwooleHttpServer;
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
     * @var ?SwooleServer
     */
    protected ?SwooleServer $server = null;

    /**
     * @var array
     */
    protected array $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        array_multisort(array_column($config['servers'], 'type'), SORT_DESC, $config['servers']);
        $this->config = $config;
    }

    /**
     * @throws NotFoundException|ReflectionException|ContainerExceptionInterface
     */
    public function start()
    {
        $table = "+--------------------+--------------------+\n|{$this->formatLength('name', 20)}|{$this->formatLength('listen', 20)}|\n+--------------------+--------------------+\n";
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
            $table .= "|{$this->formatLength($name, 20)}|{$this->formatLength($host.':'. $port, 20)}|\n";
        }
        $table .= "+--------------------+--------------------+\n";
        echo $table;
        $this->server->start();
    }

    protected function formatLength($value, $length): string
    {
        return str_pad($value, $length, ' ', STR_PAD_BOTH);
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
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws ReflectionException
     */
    protected function makeServer(int $server, string $host, int $port, int $mode, int $sockType): mixed
    {
        $server = match ($server) {
            self::SERVER_HTTP => SwooleHttpServer::class,
            self::SERVER_WEBSOCKET => SwooleWebSocketServer::class,
            self::SERVER_BASE => SwooleServer::class,
            default => throw new RuntimeException('Server type is invalid.'),
        };
        /** @var SwooleServer $server */
        $server = new $server($host, $port, $mode, $sockType);
        foreach ($this->config['callbacks'] ?? [] as $event => $callback) {
            $server->on($event, [make($callback[0]), $callback[1]]);
        }
        return $server;
    }

    /**
     * @param               $data
     * @param int|null      $workerId
     * @param callable|null $callback
     *
     * @return mixed
     */
    public function task($data, ?int $workerId = null, ?callable $callback = null): mixed
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
