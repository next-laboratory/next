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

use Max\Utils\Traits\AutoFillProperties;

class ServerConfig
{
    use AutoFillProperties;

    /**
     * @var string
     */
    protected string $name;

    /**
     * @var int
     */
    protected int $type;

    /**
     * @var string
     */
    protected string $host = '0.0.0.0';

    /**
     * @var int
     */
    protected int $port = 9501;

    /**
     * @var array
     */
    protected array $settings = [];

    /**
     * @var int|mixed
     */
    protected int $sockType = SWOOLE_SOCK_TCP;

    /**
     * @var array
     */
    protected array $callbacks = [];

    /**
     * 初始化
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->fillProperties($config);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @return int
     */
    public function getSockType(): int
    {
        return $this->sockType;
    }

    /**
     * @return array
     */
    public function getCallbacks(): array
    {
        return $this->callbacks;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }
}
