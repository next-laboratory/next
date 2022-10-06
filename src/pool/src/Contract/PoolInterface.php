<?php

namespace Max\Pool\Contract;

interface PoolInterface
{
    public function open();

    public function close();

    public function get();

    public function getPoolCapacity(): int;

    public function release($poolItem);

    public function discard($poolItem);

    public function newPoolItem();
}
