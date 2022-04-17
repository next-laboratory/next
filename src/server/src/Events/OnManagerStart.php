<?php

namespace Max\Server\Events;

class OnManagerStart
{
    public function __construct(public \Swoole\Server $server)
    {
    }
}
