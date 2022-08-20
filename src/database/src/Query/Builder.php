<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Database\Query;

use Max\Database\Collection;
use Max\Database\Contract\QueryInterface;
use Max\Utils\Traits\Conditionable;
use PDO;

class Builder
{
    use Conditionable;

    public ?array $where;

    public array $select;

    public array $from;

    public array $order;

    public array $group;

    public array $having;

    public array $join;

    public int $limit;

    public int $offset;

    public array $bindings = [];

    /**
     * @var array|string[]
     */
    protected static array $clause = [
        'aggregate',
        'select',
        'from',
        'join',
        'where',
        'group',
        'having',
        'order',
        'limit',
        'offset',
        'lock',
    ];

    /**
     * @var int[]|string[]
     */
    protected array $column;

    public function __construct(
        protected QueryInterface $query
    ) {
    }

    /**
     * @param null $alias
     *
     * @return $this
     */
    public function from(string $table, $alias = null): static
    {
        $this->from = func_get_args();

        return $this;
    }

    public function where(string $column, mixed $value, string $operator = '='): static
    {
        $this->where[] = [$column, $operator, '?'];
        $this->addBindings($value);

        return $this;
    }

    /**
     * @return $this
     */
    public function whereNull(string $column): static
    {
        $this->where[] = [$column, 'IS NULL'];

        return $this;
    }

    /**
     * @return $this
     */
    public function whereNotNull(string $column): static
    {
        $this->where[] = [$column, 'IS NULL'];

        return $this;
    }

    /**
     * @param $column
     * @param $value
     *
     * @return $this
     */
    public function whereLike($column, $value): static
    {
        return $this->where($column, $value, 'LIKE');
    }

    /**
     * @return $this
     */
    public function whereIn(string $column, array $in): static
    {
        if (! empty($in)) {
            $this->addBindings($in);
            $this->where[] = [$column, 'IN', sprintf('(%s)', rtrim(str_repeat('?, ', count($in)), ' ,'))];
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function whereRaw(string $expression, array $bindings = []): static
    {
        $this->where[] = new Expression($expression);
        $this->setBindings($bindings);

        return $this;
    }

    /**
     * @param         $table
     * @param ?string $alias
     */
    public function join($table, ?string $alias = null, string $league = 'INNER JOIN'): Join
    {
        return $this->join[] = new Join($this, $table, $alias, $league);
    }

    /**
     * @param $table
     */
    public function leftJoin($table, ?string $alias = null): Join
    {
        return $this->join($table, $alias, 'LEFT OUTER JOIN');
    }

    /**
     * @param $table
     */
    public function rightJoin($table, ?string $alias = null): Join
    {
        return $this->join($table, $alias, 'RIGHT OUTER JOIN');
    }

    /**
     * @param $column
     * @param $start
     * @param $end
     *
     * @return $this
     */
    public function whereBetween($column, $start, $end): static
    {
        $this->addBindings([$start, $end]);
        $this->where[] = [$column, 'BETWEEN', '(? AND ?)'];

        return $this;
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * @param $bindings
     */
    public function setBindings($bindings): void
    {
        if (is_array($bindings)) {
            $this->bindings = [...$this->bindings, ...$bindings];
        } else {
            $this->bindings[] = $bindings;
        }
    }

    /**
     * @return $this
     */
    public function select(array $columns = ['*']): static
    {
        $this->select = $columns;

        return $this;
    }

    /**
     * @param $column
     *
     * @return $this
     */
    public function order($column, string $order = 'ASC'): static
    {
        $this->order[] = [$column, $order];

        return $this;
    }

    /**
     * @return $this
     */
    public function latest(string $column = 'id'): static
    {
        return $this->order($column, 'DESC');
    }

    /**
     * @return $this
     */
    public function oldest(string $column = 'id'): static
    {
        return $this->order($column);
    }

    /**
     * @param $column
     *
     * @return $this
     */
    public function group($column): static
    {
        $this->group[] = $column;

        return $this;
    }

    /**
     * @param $first
     * @param $last
     *
     * @return $this
     */
    public function having($first, $last, string $operator = '='): static
    {
        $this->having[] = [$first, $operator, $last];

        return $this;
    }

    /**
     * @return $this
     */
    public function limit(int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return $this
     */
    public function offset(int $offset): static
    {
        $this->offset = $offset;

        return $this;
    }

    public function toSql(array $columns = ['*']): string
    {
        if (empty($this->select)) {
            $this->select($columns);
        } else {
            if (['*'] === $columns) {
                $this->select();
            } else {
                $this->select(array_merge($this->select, $columns));
            }
        }

        return $this->generateSelectQuery();
    }

    public function get(array $columns = ['*']): Collection
    {
        return Collection::make($this->query->select($this->toSql($columns), $this->bindings));
    }

    public function count(string|int $column = '*'): int
    {
        return $this->aggregate("COUNT({$column})");
    }

    /**
     * @param $column
     */
    public function sum($column): int
    {
        return $this->aggregate("SUM({$column})");
    }

    /**
     * @param $column
     */
    public function max($column): int
    {
        return $this->aggregate("MAX({$column})");
    }

    /**
     * @param $column
     */
    public function min($column): int
    {
        return $this->aggregate("MIN({$column})");
    }

    /**
     * @param $column
     */
    public function avg($column): int
    {
        return $this->aggregate("AVG({$column})");
    }

    public function exists(): bool
    {
        return (bool) $this->query->statement(
            sprintf('SELECT EXISTS(%s) AS MAX_EXIST', $this->toSql()),
            $this->bindings
        )->fetchColumn();
    }

    public function column(string $column, ?string $key = null): Collection
    {
        return Collection::make(
            $this->query->statement($this->toSql(array_filter([$column, $key])), $this->bindings)->fetchAll() ?: []
        )->pluck($column, $key);
    }

    /**
     * @param $id
     */
    public function find($id, array $columns = ['*'], string $identifier = 'id'): mixed
    {
        return $this->where($identifier, $id)->first($columns);
    }

    public function first(array $columns = ['*']): mixed
    {
        return $this->query->statement($this->limit(1)->toSql($columns), $this->bindings)->fetch(PDO::FETCH_ASSOC);
    }

    public function delete(): int
    {
        return $this->query->statement($this->generateDeleteQuery(), $this->bindings)->rowCount();
    }

    public function insert(array $record): int
    {
        $this->column   = array_keys($record);
        $this->bindings = array_values($record);
        $this->query->statement(
            $this->generateInsertQuery(),
            $this->bindings,
        );

        return (int) $this->query->getPdo()->lastInsertId();
    }

    public function insertMany(array $records): mixed
    {
        $this->column = array_keys($records[0]);
        $values       = [];
        foreach ($records as $record) {
            $this->addBindings(array_values($record));
            $values[] = '(' . implode(',', array_fill(0, count($records), '?')) . ')';
        }
        $query = sprintf('INSERT INTO %s (%s) VALUES %s', $this->from[0], implode(',', $this->column), implode(',', $values));
        return $this->query->statement($query, $this->bindings);
    }

    public function insertAll(array $data): array
    {
        return array_map(fn ($item) => $this->insert($item), $data);
    }

    public function update(array $data): int
    {
        return $this->query->statement($this->generateUpdateQuery($data), $this->bindings)->rowCount();
    }

    public function generateSelectQuery(): string
    {
        $query = 'SELECT ';
        foreach (static::$clause as $value) {
            $compiler = 'compile' . ucfirst($value);
            if (! empty($this->{$value})) {
                $query .= $this->{$compiler}($this);
            }
        }
        return $query;
    }

    public function generateInsertQuery(): string
    {
        $columns = implode(', ', $this->column);
        $value   = implode(', ', array_fill(0, count($this->bindings), '?'));
        $table   = $this->from[0];

        return sprintf('INSERT INTO %s(%s) VALUES(%s)', $table, $columns, $value);
    }

    public function generateUpdateQuery(array $data): string
    {
        $columns = $values = [];
        foreach ($data as $key => $value) {
            if ($value instanceof Expression) {
                $placeHolder = $value->__toString();
            } else {
                $placeHolder = '?';
                $values[]    = $value;
            }
            $columns[] = $key . ' = ' . $placeHolder;
        }

        array_unshift($this->bindings, ...$values);
        $where = empty($this->where) ? '' : $this->compileWhere();

        return sprintf('UPDATE %s SET %s%s', $this->from[0], implode(', ', $columns), $where);
    }

    public function generateDeleteQuery(): string
    {
        return sprintf('DELETE FROM %s %s', $this->from[0], $this->compileWhere());
    }

    /**
     * @param $value
     */
    protected function addBindings($value): void
    {
        if (is_array($value)) {
            array_push($this->bindings, ...$value);
        } else {
            $this->bindings[] = $value;
        }
    }

    protected function aggregate(string $expression): int
    {
        return (int) $this->query->statement(
            $this->toSql((array) ($expression . ' AS AGGREGATE')),
            $this->bindings
        )->fetchColumn();
    }

    protected function compileJoin(): string
    {
        $joins     = array_map(function (Join $item) {
            $alias = $item->alias ? 'AS ' . $item->alias : '';
            $on    = $item->on ? ('ON ' . implode(' ', $item->on)) : '';
            return ' ' . $item->league . ' ' . $item->table . ' ' . $alias . ' ' . $on;
        }, $this->join);

        return implode('', $joins);
    }

    protected function compileWhere(): string
    {
        $whereCondition = [];
        foreach ($this->where as $where) {
            $whereCondition[] = $where instanceof Expression ? $where->__toString() : implode(' ', $where);
        }
        return ' WHERE ' . implode(' AND ', $whereCondition);
    }

    protected function compileFrom(): string
    {
        return ' FROM ' . implode(' AS ', array_filter($this->from));
    }

    protected function compileSelect(): string
    {
        return implode(', ', $this->select);
    }

    protected function compileLimit(): string
    {
        return ' LIMIT ' . $this->limit;
    }

    protected function compileOffset(): string
    {
        return ' OFFSET ' . $this->offset;
    }

    protected function compileOrder(): string
    {
        $orderBy = array_map(fn ($item) => $item[0] instanceof Expression ? $item[0]->__toString() : implode(' ', $item), $this->order);
        return ' ORDER BY ' . implode(', ', $orderBy);
    }

    protected function compileGroup(): string
    {
        return ' GROUP BY ' . implode(', ', $this->group);
    }

    protected function compileHaving(): string
    {
        $having = array_map(fn ($item) => implode(' ', $item), $this->having);

        return ' HAVING ' . implode(' AND ', $having);
    }
}
