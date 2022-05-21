<?php

namespace Max\Pool\Contracts;

interface PoolInterface
{
    public function open();

    public function close();

    public function get();

    public function gc();

    public function release($poolable);
}
