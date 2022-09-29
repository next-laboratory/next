<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Database;

use Max\Utils\Traits\Conditionable;
use PDO;

class QueryBuilder
{
    use Conditionable;

    protected array $where = [];

    protected array $bindings = [];

    protected array $select;

    protected array $from;

    protected array $order;

    protected array $group;

    protected array $having;

    protected array $join;

    protected int $limit;

    protected int $offset;

    protected array $column;

    protected static array $clause = ['aggregate', 'select', 'from', 'join', 'where', 'group', 'having', 'order', 'limit', 'offset', 'lock'];

    public function __construct(
        protected Query $query,
    ) {
    }

    public function from(string $table, string $alias = ''): static
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

    public function whereNull(string $column): static
    {
        $this->where[] = [$column, 'IS NULL'];

        return $this;
    }

    public function whereNotNull(string $column): static
    {
        $this->where[] = [$column, 'IS NULL'];

        return $this;
    }

    public function whereLike($column, $value): static
    {
        return $this->where($column, $value, 'LIKE');
    }

    public function whereIn(string $column, array $in): static
    {
        if (!empty($in)) {
            $this->addBindings($in);
            $this->where[] = [$column, 'IN', sprintf('(%s)', rtrim(str_repeat('?, ', count($in)), ' ,'))];
        }

        return $this;
    }

    public function whereRaw(string $expression, array $bindings = []): static
    {
        $this->where[] = new Expression($expression);
        $this->setBindings($bindings);

        return $this;
    }

    public function join(string $table, ?string $alias = null, string $league = 'INNER JOIN'): Join
    {
        return $this->join[] = new Join($this, $table, $alias, $league);
    }

    public function leftJoin(string $table, ?string $alias = null): Join
    {
        return $this->join($table, $alias, 'LEFT OUTER JOIN');
    }

    public function rightJoin(string $table, ?string $alias = null): Join
    {
        return $this->join($table, $alias, 'RIGHT OUTER JOIN');
    }

    public function whereBetween($column, $start, $end): static
    {
        $this->addBindings([$start, $end]);
        $this->where[] = [$column, 'BETWEEN', '(? AND ?)'];

        return $this;
    }

    public function setBindings($bindings): void
    {
        if (is_array($bindings)) {
            $this->bindings = [...$this->bindings, ...$bindings];
        } else {
            $this->bindings[] = $bindings;
        }
    }

    public function select(array $columns = ['*']): static
    {
        $this->select = $columns;

        return $this;
    }

    public function orderBy(string|Expression $columnOrExpr, string $order = 'ASC'): static
    {
        $this->order[] = $columnOrExpr instanceof Expression ? [$columnOrExpr->expression] : [$columnOrExpr, $order];

        return $this;
    }

    public function orderByDesc(string|Expression $column): static
    {
        return $this->orderBy($column, 'DESC');
    }

    public function latest(string $column = 'id'): static
    {
        return $this->orderByDesc($column);
    }

    public function oldest(string $column = 'id'): static
    {
        return $this->orderBy($column);
    }

    public function groupBy($column): static
    {
        $this->group[] = $column;

        return $this;
    }

    public function having($first, $last, string $operator = '='): static
    {
        $this->having[] = [$first, $operator, $last];

        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    public function offset(int $offset): static
    {
        $this->offset = $offset;

        return $this;
    }

    public function toSql(): string
    {
        return $this->generateSelectQuery();
    }

    public function get(array $columns = ['*']): bool|array
    {
        return $this->query->select($this->select($columns)->toSql(), $this->bindings);
    }

    public function count(string|int $column = '*'): int
    {
        return $this->aggregate("COUNT({$column})");
    }

    public function sum($column): int
    {
        return $this->aggregate("SUM({$column})");
    }

    public function max($column): int
    {
        return $this->aggregate("MAX({$column})");
    }

    public function min($column): int
    {
        return $this->aggregate("MIN({$column})");
    }

    public function avg($column): int
    {
        return $this->aggregate("AVG({$column})");
    }

    public function exists(): bool
    {
        $query = sprintf('SELECT EXISTS(%s) AS MAX_EXIST', $this->select([1])->toSql());

        return (bool)$this->query->statement($query, $this->bindings)->fetchColumn();
    }

    public function column(string $column, ?string $key = null): array
    {
        $query  = $this->select(array_filter([$column, $key]))->toSql();
        $result = $this->query->select($query, $this->bindings) ?: [];

        return array_column($result, $column, $key);
    }

    public function find($id, array $columns = ['*'], string $identifier = 'id'): mixed
    {
        return $this->where($identifier, $id)->first($columns);
    }

    public function first(array $columns = ['*']): mixed
    {
        $query = $this->limit(1)->select($columns)->toSql();

        return $this->query->statement($query, $this->bindings)->fetch(PDO::FETCH_ASSOC);
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

        return (int)$this->query->getPDO()->lastInsertId();
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
        return array_map(fn($item) => $this->insert($item), $data);
    }

    public function update(array $data): int
    {
        return $this->query->statement($this->generateUpdateQuery($data), $this->bindings)->rowCount();
    }

    protected function generateSelectQuery(): string
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

    protected function generateInsertQuery(): string
    {
        $columns = implode(', ', $this->column);
        $value   = implode(', ', array_fill(0, count($this->bindings), '?'));
        $table   = $this->from[0];

        return sprintf('INSERT INTO %s(%s) VALUES(%s)', $table, $columns, $value);
    }

    protected function generateUpdateQuery(array $data): string
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

    protected function generateDeleteQuery(): string
    {
        return sprintf('DELETE FROM %s %s', $this->from[0], $this->compileWhere());
    }

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
        return (int)$this->query->statement(
            $this->select((array)($expression . ' AS AGGREGATE'))->toSql(),
            $this->bindings
        )->fetchColumn();
    }

    protected function compileJoin(): string
    {
        $joins = array_map(function(Join $item) {
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
        $orderBy = array_map(fn($item) => $item[0] instanceof Expression ? $item[0]->__toString() : implode(' ', $item), $this->order);
        return ' ORDER BY ' . implode(', ', $orderBy);
    }

    protected function compileGroup(): string
    {
        return ' GROUP BY ' . implode(', ', $this->group);
    }

    protected function compileHaving(): string
    {
        $having = array_map(fn($item) => implode(' ', $item), $this->having);

        return ' HAVING ' . implode(' AND ', $having);
    }
}
