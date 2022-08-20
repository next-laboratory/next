<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Swoole;

use Swoole\Coroutine;
use Swoole\Coroutine\Context as SwooleContext;

class Context
{
    public static function getCid(): int
    {
        return Coroutine::getCid();
    }

    public static function for(int $cid = 0): ?SwooleContext
    {
        return Coroutine::getContext($cid);
    }
}
