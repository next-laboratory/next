<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Aop\Contract;

use Closure;
use Next\Aop\JoinPoint;

interface AspectInterface
{
    public function process(JoinPoint $joinPoint, Closure $next);
}
