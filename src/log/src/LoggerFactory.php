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

use Max\Config\Repository;
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
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return static
     */
    public static function __new(Repository $repository)
    {
        return new static($repository->get('logger'));
    }

    /**
     * 这个实现不好
     *
     * @param string $name
     *
     * @return LoggerInterface
     */
    public function get(string $name = 'default'): LoggerInterface
    {
        $name = ('default' === $name) ? $this->config[$name] : $name;
        if (!$this->has($name)) {
            $logger  = new MonologLogger($name);
            $options = $this->config['logger'][isset($this->config['logger'][$name]) ? $name : $this->config['default']];
            $handler = $options['handler'];
            $logger->pushHandler(new $handler(...$options['options']));
            $this->set(strtolower($name), $logger);
        }
        return $this->logger[strtolower($name)];
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
