<?php

namespace Max\Swoole\Events;

class OnWorkerStart
{
    public function __construct(public \Swoole\Server $server, public int $workerId)
    {
    }
}
