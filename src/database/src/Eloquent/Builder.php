<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Database\Eloquent;

use Max\Database\Collection;
use Max\Database\Exceptions\ModelNotFoundException;
use Max\Database\Query\Builder as QueryBuilder;
use PDO;
use Throwable;

class Builder extends QueryBuilder
{
    protected Model $model;

    protected string $class;

    /**
     * @return $this
     */
    public function setModel(Model $model): static
    {
        $this->model = $model;
        $this->class = $model::class;
        $this->from  = [$model->getTable(), ''];

        return $this;
    }

    public function get(array $columns = ['*']): Collection
    {
        return Collection::make(
            $this->query->statement($this->toSql($columns), $this->bindings)->fetchAll(PDO::FETCH_CLASS, $this->class)
        );
    }

    public function first(array $columns = ['*']): ?Model
    {
        try {
            return $this->firstOrFail($columns);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @throws ModelNotFoundException
     */
    public function firstOrFail(array $columns = ['*']): Model
    {
        return $this->query->statement(
            $this->limit(1)->toSql($columns),
            $this->bindings
        )->fetchObject($this->class) ?: throw new ModelNotFoundException('No data was found.');
    }

    /**
     * @param $id
     */
    public function find($id, array $columns = ['*'], ?string $identifier = null): ?Model
    {
        return $this->where($identifier ?? $this->model->getKey(), $id)->first($columns);
    }

    /**
     * @param $id
     *
     * @throws ModelNotFoundException
     */
    public function findOrFail($id, array $columns = ['*'], string $identifier = 'id'): Model
    {
        return $this->where($this->model->getKey(), $id)->firstOrFail($columns);
    }
}
