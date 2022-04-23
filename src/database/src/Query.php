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

use Closure;
use Max\Database\Events\QueryExecuted;
use Max\Database\Exceptions\PoolException;
use Max\Database\Exceptions\QueryException;
use Max\Database\Pools\PDOPool;
use Max\Database\Query\Builder;
use PDO;
use PDOException;
use PDOStatement;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Database\PDOProxy;
use Throwable;

class Query
{
    /**
     * The default connection name.
     *
     * @var ?string
     */
    protected ?string $connection = null;

    /**
     * The default fetch mode for query.
     *
     * @var int
     */
    protected int $fetchMode = PDO::FETCH_ASSOC;

    /**
     * Whether it is in a transaction.
     *
     * @var bool
     */
    protected bool $inTransaction = false;

    /**
     * The property will be a PDO or PDOProxy instance while the query is in a transaction.
     *
     * @var ?PDOProxy
     */
    public ?PDOProxy $PDO = null;

    /**
     * @param PDOPool                       $PDOPool
     * @param EventDispatcherInterface|null $eventDispatcher
     */
    public function __construct(
        protected PDOPool                   $PDOPool,
        protected ?EventDispatcherInterface $eventDispatcher = null
    )
    {
    }

    /**
     * Return a new Query instance with new connection name.
     *
     * @param ?string $connection
     *
     * @return $this
     */
    public function connection(?string $connection = null): static
    {
        if (is_null($connection)) {
            return $this;
        }
        $new             = clone $this;
        $new->connection = $connection;

        return $new;
    }

    /**
     * Use query builder to build a new query.
     *
     * @param             $name
     * @param null        $alias
     *
     * @return Builder
     */
    public function table($name, $alias = null)
    {
        return (new Builder($this))->from($name, $alias);
    }

    /**
     * Execute a new select query.
     *
     * @param string $query
     * @param array  $bindings
     *
     * @return mixed
     * @throws PoolException
     * @throws QueryException
     */
    public function select(string $query, array $bindings = [])
    {
        return $this->wrap(function($PDO) use ($query, $bindings) {
            return $this->statement($PDO, $query, $bindings)->fetchAll($this->fetchMode);
        });
    }

    /**
     * @param PDOStatement $PDOStatement
     * @param array        $bindings
     */
    protected function bindValue($PDOStatement, array $bindings)
    {
        foreach ($bindings as $key => $value) {
            $PDOStatement->bindValue(
                is_string($key) ? $key : $key + 1,
                $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
    }

    /**
     * Begin a new transaction, the transaction will roll back while it throws any exception.
     *
     * @param Closure $transaction
     *
     * @return mixed
     * @throws PoolException
     * @throws QueryException
     */
    public function transaction(Closure $transaction): mixed
    {
        $new                = clone $this;
        $new->inTransaction = true;
        return $new->wrap(function($PDO) use ($transaction) {
            /** @var PDO $PDO */
            $PDO->beginTransaction();
            try {
                $result = $transaction($PDO);
                $PDO->commit();
                return $result;
            } catch (\Exception $exception) {
                $PDO->rollBack();
                throw $exception;
            } finally {
                $this->inTransaction = false;
            }
        });
    }

    /**
     * @param string $query
     * @param array  $bindings
     *
     * @return mixed
     * @throws PoolException
     * @throws QueryException
     */
    public function exec(string $query, array $bindings = [])
    {
        return $this->wrap(function($PDO) use ($query, $bindings) {
            return $this->statement($PDO, $query, $bindings);
        });
    }

    /**
     * @param        $PDO
     * @param string $query
     * @param array  $bindings
     *
     * @return PDOStatement
     */
    public function statement($PDO, string $query, array $bindings = [])
    {
        try {
            $startTime    = microtime(true);
            $PDOStatement = $PDO->prepare($query);
            $this->bindValue($PDOStatement, $bindings);
            $PDOStatement->execute();
            $this->eventDispatcher?->dispatch(
                new QueryExecuted($query, $bindings, microtime(true) - $startTime)
            );
            return $PDOStatement;
        } catch (PDOException $PDOException) {
            throw new PDOException(
                $PDOException->getMessage() . sprintf(' (SQL: %s)', $query),
                (int)$PDOException->getCode(),
                $PDOException->getPrevious()
            );
        }
    }

    /**
     * @param Closure $closure
     *
     * @return mixed
     * @throws PoolException
     * @throws QueryException
     */
    public function wrap(Closure $closure): mixed
    {
        $pool       = $this->PDOPool->getPool($this->connection);
        $retryTimes = 0;
        RETRY:
        if ($this->inTransaction) {
            if (!isset($this->PDO)) {
                $this->PDO = $pool->get(3);
            }
            $PDO = $this->PDO;
        } else {
            $PDO = $pool->get(3);
        }
        try {
            $result = $closure($PDO);
            $pool->put($PDO);
            return $result;
        } catch (Throwable $throwable) {
            $pool->put(null);
            if (++$retryTimes < 3) {
                goto RETRY;
            }
            throw new QueryException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }
    }
}
