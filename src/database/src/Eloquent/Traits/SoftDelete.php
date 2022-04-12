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

trait SoftDelete
{
    protected string $delete_at = 'delete_at';

    public function withTrashed()
    {
    }

    public function onlyTrashed()
    {
        return $this->whereNotNull($this->delete_at);
    }
}