<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Database\Eloquent\Relation;

use Max\Database\Eloquent\Builder;
use Max\Database\Eloquent\Model;

class HasMany
{
    public function __construct(
        protected Builder $builder,
        protected Model $owner,
        protected $foreignKey,
        protected $localKey
    ) {
    }
}
