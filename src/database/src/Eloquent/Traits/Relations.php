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
        $entity     = new $related;
        $foreignKey ??= $entity->getTable() . '_id';

        return new HasMany($entity->newQuery(), $this, $this->getTable() . '.' . $foreignKey, $localKey);
    }

}
