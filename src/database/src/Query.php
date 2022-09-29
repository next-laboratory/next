<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Database;

use Closure;
use Max\Database\Contract\ConnectorInterface;
use Max\Database\Event\QueryExecuted;
use PDO;
use PDOException;
use PDOStatement;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

class Query
{
    /**
     * @var PDO
     */
    protected $PDO;

    public function __construct(
        protected ConnectorInterface $connector,
        protected ?EventDispatcherInterface $eventDispatcher = null,
    ) {
    }

    public function __destruct()
    {
        if (isset($this->PDO)) {
            $this->connector->release($this->PDO);
        }
    }

    public function select(string $query, array $bindings = [], int $mode = PDO::FETCH_ASSOC, ...$args): bool|array
    {
        return $this->statement($query, $bindings)->fetchAll($mode, ...$args);
    }

    public function selectOne(string $query, array $bindings = [], int $mode = PDO::FETCH_ASSOC, ...$args): mixed
    {
        return $this->statement($query, $bindings)->fetch($mode, ...$args);
    }

    public function update(string $query, array $bindings = []): int
    {
        return $this->statement($query, $bindings)->rowCount();
    }

    public function delete(string $query, array $bindings = []): int
    {
        return $this->statement($query, $bindings)->rowCount();
    }

    public function insert(string $query, array $bindings = [], ?string $id = null): bool|string
    {
        $this->statement($query, $bindings);
        return $this->getPDO()->lastInsertId($id);
    }

    /**
     * @return false|PDOStatement
     */
    public function statement(string $query, array $bindings = [])
    {
        try {
            $start        = microtime(true);
            $PDOStatement = $this->getPDO()->prepare($query);
            foreach ($bindings as $key => $value) {
                $PDOStatement->bindValue(is_string($key) ? $key : $key + 1, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $PDOStatement->execute();
            return $PDOStatement;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage() . sprintf(' (SQL: %s)', $query), (int) $e->getCode(), $e->getPrevious());
        } finally {
            $this->eventDispatcher?->dispatch(new QueryExecuted($query, $bindings, microtime(true) - $start));
        }
    }

    public function table(string $table, string $alias = ''): QueryBuilder
    {
        return (new QueryBuilder($this))->from($table, $alias);
    }

    /**
     * @throws Throwable
     */
    public function transaction(Closure $callback)
    {
        $PDO = $this->getPDO();
        $PDO->beginTransaction();
        try {
            $result = $callback($this);
            $PDO->commit();
            return $result;
        } catch (Throwable $e) {
            $PDO->rollBack();
            throw $e;
        }
    }

    /**
     * @return PDO
     */
    public function getPDO()
    {
        if (! isset($this->PDO)) {
            $this->PDO = $this->connector->get();
        }
        return $this->PDO;
    }
}
