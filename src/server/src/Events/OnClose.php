<?php
declare(strict_types=1);

namespace Max\Server\Events;

class OnClose
{
    public function __construct(public $server, public $fd)
    {
    }
}
