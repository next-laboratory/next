<?php

namespace Max\Database;

use Max\Database\Contract\ConfigInterface;

class PDOConfig implements ConfigInterface
{
    public function __construct(
        string           $driver = 'mysql',
        string           $host = '127.0.0.1',
        int              $port = 3306,
        string           $database = '',
        protected string $user = 'root',
        protected string $password = '',
        protected array  $options = [],
        string           $unixSocket = '',
        protected string $DSN = '',
    )
    {
        if (empty($this->DSN)) {
            $this->DSN = sprintf('%s:host=%s;port=%s;', $driver, $host, $port);
            if (!empty($database)) {
                $this->DSN .= 'dbname=' . $database . ';';
            }
            if (!empty($unixSocket)) {
                $this->DSN .= 'unix_socket=' . $unixSocket . ';';
            }
        }
    }

    public function getDSN(): string
    {
        return $this->DSN;
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
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}