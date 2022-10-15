<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Database;

/**
 * @mixin QueryBuilder
 */
class Join
{
    public array $on = [];

    public function __construct(
        protected QueryBuilder $builder,
        public string $table,
        public ?string $alias = null,
        public string $league = 'INNER JOIN'
    ) {
    }

    /**
     * @param $method
     * @param $args
     *
     * @return QueryBuilder
     */
    public function __call($method, $args)
    {
        return $this->builder->{$method}(...$args);
    }

    public function on($first, $last, string $operator = '='): QueryBuilder
    {
        $this->on = [$first, $operator, $last];

        return $this->builder;
    }
}
