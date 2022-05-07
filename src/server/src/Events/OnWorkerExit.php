<?php

namespace Max\Server\Events;

use Swoole\Server;

class OnWorkerExit
{
    public function __construct(public Server $server, public int $workerId)
    {
    }
}
