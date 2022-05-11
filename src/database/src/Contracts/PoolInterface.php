<?php

namespace Max\Database\Contracts;

interface PoolInterface
{
    public function get();

    public function put($poolable);
}
