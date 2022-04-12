<?php
declare(strict_types=1);

namespace Max\Server\Events;

class OnOpen
{
    public function __construct(public $server, public $request)
    {
    }
}
