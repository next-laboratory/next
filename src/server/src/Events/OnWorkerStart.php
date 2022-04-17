<?php

namespace Max\Server\Events;

class OnWorkerStart
{
    public function __construct(public \Swoole\Server $server, public int $workerId)
    {
    }
}
