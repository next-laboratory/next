<?php

namespace Max\Swoole\Events;

use Swoole\Server;

class OnShutdown
{
    public function __construct(public Server $server)
    {
    }
}
