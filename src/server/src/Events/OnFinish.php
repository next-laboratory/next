<?php
declare(strict_types=1);

namespace Max\Server\Events;

use Swoole\Server;

class OnFinish
{
    public function __construct(public Server $server, public int $taskId, public mixed $data)
    {
    }
}
