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

namespace Max\Redis;

use Max\Utils\Traits\AutoFillProperties;

class RedisConfig
{
    use AutoFillProperties;

    public const OPTION_NAME           = 'name';
    public const OPTION_HOST           = 'host';
    public const OPTION_PORT           = 'port';
    public const OPTION_AUTH           = 'auth';
    public const OPTION_DATABASE       = 'database';
    public const OPTION_TIMEOUT        = 'timeout';
    public const OPTION_READ_TIMEOUT   = 'readTimeout';
    public const OPTION_RETRY_INTERVAL = 'retryInterval';
    public const OPTION_RESERVED       = 'reserved';
    public const OPTION_POOL_SIZE      = 'poolSize';
    public const OPTION_AUTO_FILL      = 'autofill';

    /**
     * @var string
     */
    protected string $name;
    /**
     * @var string
     */
    protected string $host = '127.0.0.1';

    /**
     * @var int
     */
    protected int $port = 6379;

    /**
     * @var string
     */
    protected string $auth = '';

    /**
     * @var int
     */
    protected int $database = 0;
    /**
     * @var int
     */
    protected int $timeout = 3;
    /**
     * @var int
     */
    protected int $readTimeout = 3;
    /**
     * @var int
     */
    protected int $retryInterval = 3;
    /**
     * @var ?string
     */
    protected ?string $reserved = null;
    /**
     * @var int
     */
    protected int $poolSize = 64;

    /**
     * @var bool
     */
    protected bool $autofill = false;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->fillProperties($config);
    }

    /**
     * @return string
     */
    public function getAuth(): string
    {
        return $this->auth;
    }

    /**
     * @return bool
     */
    public function isAutofill(): bool
    {
        return $this->autofill;
    }

    /**
     * @return int
     */
    public function getDatabase(): int
    {
        return $this->database;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @return int
     */
    public function getReadTimeout(): int
    {
        return $this->readTimeout;
    }

    /**
     * @return int
     */
    public function getRetryInterval(): int
    {
        return $this->retryInterval;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return ?string
     */
    public function getReserved(): ?string
    {
        return $this->reserved;
    }

    /**
     * @return int
     */
    public function getPoolSize(): int
    {
        return $this->poolSize;
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
}
