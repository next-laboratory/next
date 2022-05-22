<?php

namespace Max\Redis\Contracts;

interface ConnectorInterface
{
    public function get(): \Redis;

    public function release(\Redis $redis);
}
