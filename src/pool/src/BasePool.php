<?php

namespace Max\Pool;

use Max\Pool\Contracts\Poolable;
use Max\Pool\Contracts\PoolInterface;

class BasePool implements PoolInterface
{
    public function open()
    {
        // TODO: Implement open() method.
    }

    public function close()
    {
        // TODO: Implement close() method.
    }

    public function gc()
    {
        // TODO: Implement gc() method.
    }

    public function release(Poolable $poolable)
    {
        // TODO: Implement release() method.
    }

    public function getName(): string
    {
        // TODO: Implement getName() method.
    }

    public function get(): Poolable
    {
        // TODO: Implement get() method.
    }
}
