<?php

namespace Max\Server\Events;

use Swoole\Server;

class OnBeforeShutdown
{
    public function __construct(public Server $server)
    {
    }
}
