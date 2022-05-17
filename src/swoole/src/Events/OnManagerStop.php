<?php

namespace Max\Swoole\Events;

use Swoole\Server;

class OnManagerStop
{
    public function __construct(public Server $server)
    {
    }
}
