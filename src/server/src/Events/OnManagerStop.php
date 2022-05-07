<?php

namespace Max\Server\Events;

use Swoole\Server;

class OnManagerStop
{
    public function __construct(public Server $server)
    {
    }
}
