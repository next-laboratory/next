<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Database\Query;

/**
 * @mixin Builder
 */
class Join
{
    public string $table;

    /**
     * @var ?string
     */
    public ?string $alias = null;

    public string $league;

    public array $on = [];

    protected Builder $builder;

    public function __construct(Builder $builder, string $table, ?string $alias = null, string $league = 'INNER JOIN')
    {
        $this->builder = $builder;
        $this->table   = $table;
        $this->league  = $league;
        $this->alias   = $alias;
    }

    /**
     * @param $method
     * @param $args
     *
     * @return Builder
     */
    public function __call($method, $args)
    {
        return $this->builder->{$method}(...$args);
    }

    /**
     * @param $first
     * @param $last
     *
     * @return Builder
     */
    public function on($first, $last, string $operator = '=')
    {
        $this->on = [$first, $operator, $last];

        return $this->builder;
    }
}
