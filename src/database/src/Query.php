<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Database;

use Next\Database\Event\QueryExecuted;
use Psr\EventDispatcher\EventDispatcherInterface;

class Query
{
    public function __construct(
        protected \PDO $PDO,
        protected ?EventDispatcherInterface $eventDispatcher = null,
    ) {
    }

    public function select(string $query, array $bindings = [], int $mode = \PDO::FETCH_ASSOC, ...$args): bool|array
    {
        return $this->statement($query, $bindings)->fetchAll($mode, ...$args);
    }

    public function selectOne(string $query, array $bindings = [], int $mode = \PDO::FETCH_ASSOC, ...$args): mixed
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

    public function statement(string $query, array $bindings = []): bool|\PDOStatement
    {
        try {
            $start        = microtime(true);
            $PDOStatement = $this->getPDO()->prepare($query);
            foreach ($bindings as $key => $value) {
                $PDOStatement->bindValue(is_string($key) ? $key : $key + 1, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
            }
            $PDOStatement->execute();
            return $PDOStatement;
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage() . sprintf(' (SQL: %s)', $query), (int) $e->getCode(), $e->getPrevious());
        } finally {
            $this->eventDispatcher?->dispatch(new QueryExecuted($query, $bindings, microtime(true) - $start));
        }
    }

    public function beginTransaction(): bool
    {
        return $this->PDO->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->PDO->commit();
    }

    public function rollBack(): bool
    {
        return $this->PDO->rollBack();
    }

    /**
     * @throws \Throwable
     */
    public function transaction(\Closure $callback, ...$args)
    {
        $this->beginTransaction();
        try {
            $result = $callback($this, ...$args);
            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * @return \PDO
     */
    public function getPDO()
    {
        return $this->PDO;
    }
}
