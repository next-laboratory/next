<?php

namespace Max\Swoole\Table;

use ErrorException;
use Swoole\Table;

class Manager
{
    protected static array $tables      = [];
    protected static bool  $initialized = false;

    public static function init(): void
    {
        if (!static::$initialized) {
            // user表，主键fd
            $user = new Table(1 << 10);
            $user->column('name', Table::TYPE_STRING, 8);
            $user->column('age', Table::TYPE_INT, 2);
            $user->create();
            static::$tables['user'] = $user;
        }
    }

    /**
     * @throws ErrorException
     */
    public static function get(string $name): Table
    {
        if (isset(static::$tables[$name])) {
            return static::$tables[$name];
        }
        throw new ErrorException(sprintf('Table %s does not exist', $name));
    }
}
