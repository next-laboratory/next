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

namespace Max\Database\Query;

use Max\Database\Collection;
use Max\Database\Contracts\QueryInterface;
use Max\Utils\Traits\Conditionable;
use PDO;

class Builder
{
    use Conditionable;

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
        'lock'
    ];

    /**
     * @var array|null
     */
    public ?array $where;

    /**
     * @var array
     */
    public array $select;

    /**
     * @var array
     */
    public array $from;

    /**
     * @var array
     */
    public array $order;

    /**
     * @var array
     */
    public array $group;

    /**
     * @var array
     */
    public array $having;

    /**
     * @var array
     */
    public array $join;

    /**
     * @var int
     */
    public int $limit;

    /**
     * @var int
     */
    public int $offset;

    /**
     * @var array
     */
    public array $bindings = [];

    /**
     * @var int[]|string[]
     */
    protected array $column;

    /**
     * @param QueryInterface $query
     */
    public function __construct(protected QueryInterface $query)
    {
    }

    /**
     * @param string $table
     * @param null   $alias
     *
     * @return $this
     */
    public function from(string $table, $alias = null): static
    {
        $this->from = func_get_args();

        return $this;
    }

    /**
     * @param string $column
     * @param        $value
     * @param string $operator
     *
     * @return $this
     */
    public function where(string $column, $value, string $operator = '='): static
    {
        $this->where[] = [$column, $operator, '?'];
        $this->addBindings($value);

        return $this;
    }

    /**
     * @param string $column
     *
     * @return $this
     */
    public function whereNull(string $column): static
    {
        $this->where[] = [$column, 'IS NULL'];

        return $this;
    }

    /**
     * @param string $column
     *
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
     * @param string $column
     * @param array  $in
     *
     * @return $this
     */
    public function whereIn(string $column, array $in): static
    {
        if (!empty($in)) {
            $this->addBindings($in);
            $this->where[] = [$column, 'IN', sprintf('(%s)', rtrim(str_repeat('?, ', count($in)), ' ,'))];
        }

        return $this;
    }

    /**
     * @param string $expression
     * @param array  $bindings
     *
     * @return $this
     */
    public function whereRaw(string $expression, array $bindings = []): static
    {
        $this->where[] = new Expression($expression);
        $this->setBindings($bindings);

        return $this;
    }

    /**
     * @param          $table
     * @param  ?string $alias
     * @param string   $league
     *
     * @return Join
     */
    public function join($table, ?string $alias = null, string $league = 'INNER JOIN'): Join
    {
        return $this->join[] = new Join($this, $table, $alias, $league);
    }

    /**
     * @param             $table
     * @param string|null $alias
     *
     * @return Join
     */
    public function leftJoin($table, ?string $alias = null): Join
    {
        return $this->join($table, $alias, 'LEFT OUTER JOIN');
    }

    /**
     * @param             $table
     * @param string|null $alias
     *
     * @return Join
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

    /**
     * @param $value
     *
     * @return void
     */
    protected function addBindings($value): void
    {
        if (is_array($value)) {
            array_push($this->bindings, ...$value);
        } else {
            $this->bindings[] = $value;
        }
    }

    /**
     * @return array
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * @param $bindings
     *
     * @return void
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
     * @param array $columns
     *
     * @return $this
     */
    public function select(array $columns = ['*']): static
    {
        $this->select = $columns;

        return $this;
    }

    /**
     * @param        $column
     * @param string $order
     *
     * @return $this
     */
    public function order($column, string $order = 'ASC'): static
    {
        $this->order[] = [$column, $order];

        return $this;
    }

    /**
     * @param string $column
     *
     * @return $this
     */
    public function latest(string $column = 'id'): static
    {
        return $this->order($column, 'DESC');
    }

    /**
     * @param string $column
     *
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
     * @param        $first
     * @param        $last
     *
     * @param string $operator
     *
     * @return $this
     */
    public function having($first, $last, string $operator = '='): static
    {
        $this->having[] = [$first, $operator, $last];

        return $this;
    }

    /**
     * @param int $limit
     *
     * @return $this
     */
    public function limit(int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @param int $offset
     *
     * @return $this
     */
    public function offset(int $offset): static
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @param array $columns
     *
     * @return string
     */
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

    /**
     * @param array $columns
     *
     * @return Collection
     */
    public function get(array $columns = ['*']): Collection
    {
        return Collection::make($this->query->select($this->toSql($columns), $this->bindings));
    }

    /**
     * @param string|int $column
     *
     * @return int
     */
    public function count(string|int $column = '*'): int
    {
        return $this->aggregate("COUNT($column)");
    }

    /**
     * @param $column
     *
     * @return int
     */
    public function sum($column): int
    {
        return $this->aggregate("SUM($column)");
    }

    /**
     * @param $column
     *
     * @return int
     */
    public function max($column): int
    {
        return $this->aggregate("MAX($column)");
    }

    /**
     * @param $column
     *
     * @return int
     */
    public function min($column): int
    {
        return $this->aggregate("MIN($column)");
    }

    /**
     * @param $column
     *
     * @return int
     */
    public function avg($column): int
    {
        return $this->aggregate("AVG($column)");
    }

    /**
     * @param string $expression
     *
     * @return int
     */
    protected function aggregate(string $expression): int
    {
        return (int)$this->query->statement(
            $this->toSql((array)($expression . ' AS AGGREGATE')),
            $this->bindings
        )->fetchColumn();
    }

    /**
     * @return bool
     */
    public function exists(): bool
    {
        return (bool)$this->query->statement(
            sprintf('SELECT EXISTS(%s) AS MAX_EXIST', $this->toSql()), $this->bindings
        )->fetchColumn();
    }

    /**
     * @param string      $column
     * @param string|null $key
     *
     * @return Collection
     */
    public function column(string $column, ?string $key = null): Collection
    {
        return Collection::make(
            $this->query->statement($this->toSql(array_filter([$column, $key])), $this->bindings,)->fetchAll() ?: []
        )->pluck($column, $key);
    }

    /**
     * @param        $id
     * @param array  $columns
     * @param string $identifier
     *
     * @return mixed
     */
    public function find($id, array $columns = ['*'], string $identifier = 'id'): mixed
    {
        return $this->where($identifier, $id)->first($columns);
    }

    /**
     * @param array $columns
     *
     * @return mixed
     */
    public function first(array $columns = ['*']): mixed
    {
        return $this->query->statement($this->limit(1)->toSql($columns), $this->bindings)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return $this->query->statement($this->generateDeleteQuery(), $this->bindings,)->rowCount();
    }

    /**
     * @param array $record
     *
     * @return int
     */
    public function insert(array $record): int
    {
        $this->column   = array_keys($record);
        $this->bindings = array_values($record);
        $this->query->statement(
            $this->generateInsertQuery(),
            $this->bindings,
        );

        return (int)$this->query->getPdo()->lastInsertId();
    }

    /**
     * @param array $records
     *
     * @return mixed
     */
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

    /**
     * @param array $data
     *
     * @return array
     */
    public function insertAll(array $data): array
    {
        return array_map(fn($item) => $this->insert($item), $data);
    }

    /**
     * @param array $data
     *
     * @return int
     */
    public function update(array $data): int
    {
        return $this->query->statement($this->generateUpdateQuery($data), $this->bindings)->rowCount();
    }

    /**
     * @return string
     */
    protected function compileJoin(): string
    {
        $joins = array_map(function(Join $item) {
            $alias = $item->alias ? 'AS ' . $item->alias : '';
            $on    = $item->on ? ('ON ' . implode(' ', $item->on)) : '';
            return ' ' . $item->league . ' ' . $item->table . ' ' . $alias . ' ' . $on;
        }, $this->join);

        return implode('', $joins);
    }

    /**
     * @return string
     */
    protected function compileWhere(): string
    {
        $whereCondition = [];
        foreach ($this->where as $where) {
            $whereCondition[] = $where instanceof Expression ? $where->__toString() : implode(' ', $where);
        }
        return ' WHERE ' . implode(' AND ', $whereCondition);
    }

    /**
     * @return string
     */
    protected function compileFrom(): string
    {
        return ' FROM ' . implode(' AS ', array_filter($this->from));
    }

    /**
     * @return string
     */
    protected function compileSelect(): string
    {
        return implode(', ', $this->select);
    }

    /**
     * @return string
     */
    protected function compileLimit(): string
    {
        return ' LIMIT ' . $this->limit;
    }

    /**
     * @return string
     */
    protected function compileOffset(): string
    {
        return ' OFFSET ' . $this->offset;
    }

    /**
     * @return string
     */
    protected function compileOrder(): string
    {
        $orderBy = array_map(fn($item) => $item[0] instanceof Expression ? $item[0]->__toString() : implode(' ', $item), $this->order);
        return ' ORDER BY ' . implode(', ', $orderBy);
    }

    /**
     * @return string
     */
    protected function compileGroup(): string
    {
        return ' GROUP BY ' . implode(', ', $this->group);
    }

    /**
     * @return string
     */
    protected function compileHaving(): string
    {
        $having = array_map(fn($item) => implode(' ', $item), $this->having);

        return ' HAVING ' . implode(' AND ', $having);
    }

    /**
     * @return string
     */
    public function generateSelectQuery(): string
    {
        $query = 'SELECT ';
        foreach (static::$clause as $value) {
            $compiler = 'compile' . ucfirst($value);
            if (!empty($this->{$value})) {
                $query .= $this->{$compiler}($this);
            }
        }
        return $query;
    }

    /**
     * @return string
     */
    public function generateInsertQuery(): string
    {
        $columns = implode(', ', $this->column);
        $value   = implode(', ', array_fill(0, count($this->bindings), '?'));
        $table   = $this->from[0];

        return sprintf('INSERT INTO %s(%s) VALUES(%s)', $table, $columns, $value);
    }

    /**
     * @param array $data
     *
     * @return string
     */
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

    /**
     * @return string
     */
    public function generateDeleteQuery(): string
    {
        return sprintf('DELETE FROM %s %s', $this->from[0], $this->compileWhere());
    }

}
