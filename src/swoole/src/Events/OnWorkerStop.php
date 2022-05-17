<?php

namespace Max\Swoole\Events;

use Swoole\Server;

class OnWorkerStop
{
    public function __construct(public Server $server, public int $workerId)
    {
    }
}
