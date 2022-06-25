<?php

namespace Max\JWT\BlackLists;

use Max\JWT\Contracts\BlackListInterface;

class ApcBlackListAdapter implements BlackListInterface
{
    /**
     * @param string $token
     *
     * @return void
     */
    public function add(string $token)
    {
        apc_add($token, true);
    }

    /**
     * @param string $token
     *
     * @return bool
     */
    public function isIn(string $token): bool
    {
        return apc_exists($token);
    }

    /**
     * @param string $token
     *
     * @return void
     */
    public function remove(string $token)
    {
        apc_delete($token);
    }
}
