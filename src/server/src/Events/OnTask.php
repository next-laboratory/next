<?php
declare(strict_types=1);

namespace Max\Server\Events;

use Swoole\Server;

class OnTask
{
    public function __construct(public Server $server, public int $taskId, public int $workerId, public mixed $data)
    {
    }
}
