<?php
declare(strict_types=1);

namespace Max\Server\Events;

class OnMessage
{
    public function __construct(public $server, public $frame)
    {
    }
}
