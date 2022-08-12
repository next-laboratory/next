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
    public array $on = [];

    public function __construct(
        protected Builder $builder,
        public string $table,
        public ?string $alias = null,
        public string $league = 'INNER JOIN'
    ) {
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
     * @param        $first
     * @param        $last
     * @param string $operator
     *
     * @return Builder
     */
    public function on($first, $last, string $operator = '='): Builder
    {
        $this->on = [$first, $operator, $last];

        return $this->builder;
    }
}
