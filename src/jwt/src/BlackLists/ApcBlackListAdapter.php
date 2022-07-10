<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\JWT\BlackLists;

use Max\JWT\Contracts\BlackListInterface;

class ApcBlackListAdapter implements BlackListInterface
{
    public function add(string $token)
    {
        apc_add($token, true);
    }

    public function isIn(string $token): bool
    {
        return apc_exists($token);
    }

    public function remove(string $token)
    {
        apc_delete($token);
    }
}
