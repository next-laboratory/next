<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Database;

use Max\Utils\Traits\AutoFillProperties;
use PDO;

class DBConfig
{
    use AutoFillProperties;

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
     * 默认配置.
     */
    protected const DEFAULT_OPTIONS = [
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_CASE       => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE    => PDO::ERRMODE_EXCEPTION,
        //        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        //        PDO::ATTR_STRINGIFY_FETCHES => false,
        //        PDO::ATTR_EMULATE_PREPARES  => false,
    ];

    protected string $name;

    protected string $driver = 'mysql';

    protected string $host = '127.0.0.1';

    protected int $port = 3306;

    protected string $user = 'root';

    protected string $password = '';

    protected string $database = '';

    protected string $charset = 'utf8mb4';

    protected int $poolSize = 64;

    protected array $options = [];

    protected ?string $unixSocket = null;

    protected bool $autofill = false;

    protected string $connector = '';

    protected string $dsn = '';

    public function __construct(array $config)
    {
        $this->fillProperties($config);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDriver(): string
    {
        return $this->driver;
    }

    public function isAutofill(): bool
    {
        return $this->autofill;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getDatabase(): string
    {
        return $this->database;
    }

    public function getCharset(): string
    {
        return $this->charset;
    }

    public function getPoolSize(): int
    {
        return $this->poolSize;
    }

    public function getUnixSocket(): ?string
    {
        return $this->unixSocket;
    }

    public function getDsn(): string
    {
        if (! empty($this->dsn)) {
            return $this->dsn;
        }
        return sprintf('%s:host=%s;dbname=%s;', $this->driver, $this->host, $this->database);
    }

    public function getOptions(): array
    {
        return array_replace_recursive(self::DEFAULT_OPTIONS, $this->options);
    }
}
