<?php

namespace Max\Server\Events;

class OnStart
{
    public function __construct(public \Swoole\Server $server)
    {
    }
}
