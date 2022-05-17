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

namespace Max\Swoole\WebSocket\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class WebSocketHandler
{
    /**
     * @param string $path 路径
     */
    public function __construct(public string $path)
    {
    }
}
