<?php

namespace Max\Pool;

use Exception;
use Max\Pool\Contract\PoolInterface;
use Max\Pool\Contract\PoolItemInterface;
use Throwable;

class BasePoolItem implements PoolItemInterface
{
    protected bool $failed = false;

    public function __construct(
        protected PoolInterface $pool,
        protected object $object,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function __call(string $name, array $arguments)
    {
        try {
            if ($this->failed) {
                throw new Exception('Object unavailable');
            }
            return $this->object->{$name}(...$arguments);
        } catch (Throwable $e) {
            $this->failed = true;
            throw $e;
        }
    }

    public function __destruct()
    {
        if ($this->failed) {
            $this->pool->discard($this);
        } else {
            $this->pool->release($this);
        }
    }
}
