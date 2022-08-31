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
use Max\Database\Contract\QueryInterface;
use Max\Database\Event\QueryExecuted;
use Max\Database\Query\Builder;
use PDO;
use PDOException;
use PDOStatement;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Database\PDOProxy;

class Query implements QueryInterface
{
    /**
     * @var PDO|PDOProxy
     */
    protected mixed $connection;

    public function __construct(
        protected ConnectorInterface $connector,
        protected ?EventDispatcherInterface $eventDispatcher = null
    ) {
        $this->connection = $this->connector->get();
    }

    /**
     * @return false|PDOStatement
     */
    public function statement(string $query, array $bindings = [])
    {
        try {
            $executedAt   = microtime(true);
            $PDOStatement = $this->connection->prepare($query);
            foreach ($bindings as $key => $value) {
                $PDOStatement->bindValue(
                    is_string($key) ? $key : $key + 1,
                    $value,
                    is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
                );
            }
            $PDOStatement->execute();
            $this->eventDispatcher?->dispatch(
                new QueryExecuted($query, $bindings, $executedAt)
            );
            return $PDOStatement;
        } catch (PDOException $PDOException) {
            throw new PDOException(
                $PDOException->getMessage() . sprintf(' (SQL: %s)', $query),
                (int) $PDOException->getCode(),
                $PDOException->getPrevious()
            );
        }
    }

    public function getPDO(): PDO
    {
        return $this->connection;
    }

    /**
     * @param ...$args
     */
    public function select(string $query, array $bindings = [], int $mode = PDO::FETCH_ASSOC, ...$args): bool|array
    {
        return $this->statement($query, $bindings)->fetchAll($mode, ...$args);
    }

    /**
     * @param ...$args
     *
     * @return mixed
     */
    public function selectOne(string $query, array $bindings = [], int $mode = PDO::FETCH_ASSOC, ...$args)
    {
        return $this->statement($query, $bindings)->fetch($mode, ...$args);
    }

    /**
     * @return Builder
     */
    public function table(string $table, ?string $alias = null)
    {
        return (new Builder($this))->from($table, $alias);
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
     * @return bool
     */
    public function begin()
    {
        return $this->connection->beginTransaction();
    }

    /**
     * @return bool
     */
    public function commit()
    {
        return $this->connection->commit();
    }

    /**
     * @return bool
     */
    public function rollBack()
    {
        return $this->connection->rollBack();
    }

    /**
     * @throws \Throwable
     * @return mixed
     */
    public function transaction(Closure $transaction)
    {
        $this->begin();
        try {
            $result = ($transaction)($this);
            $this->commit();
            return $result;
        } catch (\Throwable $throwable) {
            $this->rollBack();
            throw $throwable;
        }
    }
}
