<?php

namespace Next\Pool;

use Next\Pool\Contract\PoolInterface;
use RuntimeException;
use SplQueue;

abstract class BasePool implements PoolInterface
{
    protected SplQueue $splQueue;
    protected bool     $isOpen      = false;
    protected int      $currentSize = 0;

    public function open()
    {
        if (!$this->isOpen) {
            $this->splQueue = new SplQueue();
            $this->isOpen   = true;
        } else {
            throw new RuntimeException('Pool is opened');
        }
    }

    public function get()
    {
        $this->isOpen();
        $isMaximum = $this->currentSize >= $this->getPoolCapacity();
        if ($this->splQueue->isEmpty() && $isMaximum) {
            throw new RuntimeException('Too many connections');
        }
        if (!$isMaximum) {
            $this->splQueue->enqueue($this->newPoolItem());
            $this->currentSize++;
        }
        return $this->splQueue->dequeue();
    }

    public function release($poolItem)
    {
        $this->isOpen();
        if ($this->splQueue->count() < $this->getPoolCapacity()) {
            $this->splQueue->enqueue($poolItem);
        }
    }

    public function discard($poolItem)
    {
        $this->isOpen();
        $this->currentSize--;
    }

    protected function isOpen()
    {
        if (!$this->isOpen) {
            throw new RuntimeException('Pool is not opened');
        }
    }

    public function close()
    {
        $this->isOpen();
        $this->splQueue = new SplQueue();
        $this->isOpen   = false;
    }
}
