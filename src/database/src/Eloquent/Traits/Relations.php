<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Database\Eloquent\Traits;

use Max\Database\Eloquent\Model;
use Max\Database\Eloquent\Relations\HasMany;

trait Relations
{
    protected function hasOne($related, $foreignKey = null, $localKey = null)
    {
    }

    protected function hasMany($related, $foreignKey = null, $localKey = null)
    {
        /** @var Model $entity */
        $entity     = new $related();
        $foreignKey ??= $entity->getTable() . '_id';

        return new HasMany($entity->newQuery(), $this, $this->getTable() . '.' . $foreignKey, $localKey);
    }
}
