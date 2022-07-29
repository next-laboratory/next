<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Redis;

use Closure;
use Max\Redis\Contracts\ConnectorInterface;

/**
 * @mixin \Redis
 */
class Redis
{
    public function __construct(
        protected ConnectorInterface $connector
    ) {
    }

    public function __call(string $name, array $arguments)
    {
        return $this->getHandler()->{$name}(...$arguments);
    }

    public function getHandler()
    {
        return $this->connector->get();
    }

    public function wrap(Closure $callable)
    {
        return $callable($this->getHandler());
    }
}
