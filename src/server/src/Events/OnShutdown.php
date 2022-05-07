<?php

namespace Max\Server\Events;

use Swoole\Server;

class OnShutdown
{
    public function __construct(public Server $server)
    {
    }
}
