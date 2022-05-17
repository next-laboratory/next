<?php

namespace Max\Swoole\Events;

class OnStart
{
    public function __construct(public \Swoole\Server $server)
    {
    }
}
