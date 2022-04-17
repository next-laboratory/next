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
use Max\Di\Contracts\ClassAttribute;
use Max\WebSocket\RouteCollector;

#[Attribute(Attribute::TARGET_CLASS)]
class WebSocketHandler implements ClassAttribute
{
    /**
     * @param string $path 路径
     */
    public function __construct(protected string $path)
    {
    }

    /**
     * @param \ReflectionClass $reflectionClass
     * @return void
     */
    public function handle(\ReflectionClass $reflectionClass)
    {
        RouteCollector::addRoute($this->path, new ($reflectionClass->getName()));
    }
}
