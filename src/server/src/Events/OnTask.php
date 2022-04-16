<?php

declare(strict_types=1);

/**
 * This file is part of the Max package.
 *
 * (c) Cheng Yao <987861463@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Max\Server\Events;

use Swoole\Server;

class OnTask
{
    public function __construct(public Server $server, public int $taskId, public int $workerId, public mixed $data)
    {
    }
}
