<?php

namespace Max\Swoole\Events;

class OnManagerStart
{
    public function __construct(public \Swoole\Server $server)
    {
    }
}
