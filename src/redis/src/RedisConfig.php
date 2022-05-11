<?php

namespace Max\Redis;

use Max\Utils\Traits\AutoFillProperties;

class RedisConfig
{
    use AutoFillProperties;

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
     * @var string
     */
    protected ?string $reserved = null;
    /**
     * @var int
     */
    protected int $poolSize = 64;

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
