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

namespace Max\Log;

use RuntimeException;
use Max\Config\Contracts\ConfigInterface;
use Monolog\Logger as MonologLogger;
use Psr\Log\LoggerInterface;

class LoggerFactory
{
    /**
     * @var array
     */
    protected array $logger = [];

    /**
     * @var array
     */
    protected array $config;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config->get('logger');
    }

    /**
     * 这个实现不好
     *
     * @param string $name
     *
     * @return LoggerInterface
     */
    public function get(?string $name = null): LoggerInterface
    {
        $name ??= $this->config['default'] ?? null;
        if (is_null($name)) {
            throw new \InvalidArgumentException('日志配置文件有误');
        }
        $name = strtolower($name);
        if (!$this->has($name)) {
            if (!isset($this->config['logger'][$name])) {
                throw new RuntimeException('日志句柄不存在');
            }
            $logger  = new MonologLogger($name);
            $options = $this->config['logger'][$name];
            $handler = $options['handler'];
            $logger->pushHandler(new $handler(...$options['options']));
            $this->set($name, $logger);
        }
        return $this->logger[$name];
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function has($name)
    {
        return isset($this->logger[strtolower($name)]);
    }

    /**
     * @param                 $name
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function set($name, LoggerInterface $logger)
    {
        $this->logger[strtolower($name)] = $logger;
    }
}
