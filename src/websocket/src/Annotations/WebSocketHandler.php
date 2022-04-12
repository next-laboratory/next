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

namespace Max\WebSocket\Annotations;

use Attribute;
use Max\Di\Annotations\ClassAnnotation;

#[Attribute(Attribute::TARGET_CLASS)]
class WebSocketHandler extends ClassAnnotation
{
    /**
     * @param string $path 路径
     */
    public function __construct(protected string $path)
    {
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
}
