<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Next\Swoole\Table;

use Closure;
use InvalidArgumentException;
use RuntimeException;
use Swoole\Table;

class Manager
{
    protected static array $tables = [];

    public static function get(string $name): Table
    {
        if (isset(static::$tables[$name])) {
            return static::$tables[$name];
        }
        throw new RuntimeException(sprintf('Table %s does not exist', $name));
    }

    public static function create(string $tableName, Closure $closure, int $size = 4096): void
    {
        if (isset(static::$tables[$tableName])) {
            throw new InvalidArgumentException('Table已经存在');
        }
        $table = new Table($size);
        $closure($table);
        $table->create();
        static::$tables[$tableName] = $table;
    }
}
