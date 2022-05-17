<?php

namespace Max\Swoole\Events;

use Swoole\Server;

class OnBeforeShutdown
{
    public function __construct(public Server $server)
    {
    }
}
