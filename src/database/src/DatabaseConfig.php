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

namespace Max\Database;

use Max\Utils\Traits\AutoFillProperties;
use PDO;

class DatabaseConfig
{
    use AutoFillProperties;

    public const OPTION_NAME = 'name';
    public const OPTION_DRIVER = 'driver';
    public const OPTION_HOST = 'host';
    public const OPTION_PORT = 'post';
    public const OPTION_USER = 'user';
    public const OPTION_PASSWORD = 'password';
    public const OPTION_DB_NAME = 'database';
    public const OPTION_CHARSET = 'charset';
    public const OPTION_POOL_SIZE = 'poolSize';
    public const OPTION_OPTIONS = 'options';
    public const OPTION_UNIX_SOCKET = 'unixSocket';
    public const OPTION_DSN = 'dsn';
    public const OPTION_AUTO_FILL = 'autofill';

    /**
     * 默认配置
     */
    protected const DEFAULT_OPTIONS = [
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        //        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        //        PDO::ATTR_STRINGIFY_FETCHES => false,
        //        PDO::ATTR_EMULATE_PREPARES  => false,
    ];

    /**
     * @var string
     */
    protected string $name;
    /**
     * @var string
     */
    protected string $driver = 'mysql';
    /**
     * @var string
     */
    protected string $host = '127.0.0.1';
    /**
     * @var int
     */
    protected int $port = 3306;
    /**
     * @var string
     */
    protected string $user = 'root';
    /**
     * @var string
     */
    protected string $password = '';
    /**
     * @var string
     */
    protected string $database = '';
    /**
     * @var string
     */
    protected string $charset = 'utf8mb4';
    /**
     * @var int
     */
    protected int $poolSize = 64;

    /**
     * @var array
     */
    protected array $options = [];

    /**
     * @var string|null
     */
    protected ?string $unixSocket = null;

    /**
     * @var bool
     */
    protected bool $autofill = false;

    /**
     * @var string
     */
    protected string $dsn = '';

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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * @return bool
     */
    public function isAutofill(): bool
    {
        return $this->autofill;
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
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getDatabase(): string
    {
        return $this->database;
    }

    /**
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * @return int
     */
    public function getPoolSize(): int
    {
        return $this->poolSize;
    }

    /**
     * @return string|null
     */
    public function getUnixSocket(): ?string
    {
        return $this->unixSocket;
    }

    /**
     * @return string
     */
    public function getDsn(): string
    {
        if (!empty($this->dsn)) {
            return $this->dsn;
        }
        return sprintf('%s:host=%s;dbname=%s;', $this->driver, $this->host, $this->database);
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return array_replace_recursive(self::DEFAULT_OPTIONS, $this->options);
    }
}
