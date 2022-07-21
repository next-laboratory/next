<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Cache\Handlers;

use Max\Redis\Connectors\BaseConnector;
use Max\Utils\Traits\AutoFillProperties;

class RedisHandler extends CacheHandler
{
    use AutoFillProperties;

    protected string $connector = BaseConnector::class;
    protected array  $config    = [];

    public function __construct(array $config)
    {
        $this->fillProperties($config);
        $this->handler = new $this->connector();
    }

    /**
     * {@inheritDoc}
     */
    public function delete($key)
    {
        return (bool)$this->handler->del($key);
    }

    /**
     * {@inheritDoc}
     */
    public function has($key)
    {
        return (bool)$this->handler->exists($key);
    }

    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        return $this->handler->flushAll();
    }
}
