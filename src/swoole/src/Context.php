<?php

namespace Max\Swoole;

use Swoole\Coroutine;
use Swoole\Coroutine\Context as SwooleContext;

class Context
{
    public static function getCid(): int
    {
        return Coroutine::getCid();
    }

    /**
     * @param int $cid
     *
     * @return SwooleContext|null
     */
    public static function for(int $cid = 0): ?SwooleContext
    {
        return Coroutine::getContext($cid);
    }
}
