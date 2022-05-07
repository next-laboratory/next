<?php

namespace Max\Server\Events;

use Swoole\Server;

class OnWorkerStop
{
    public function __construct(public Server $server, public int $workerId)
    {
    }
}
