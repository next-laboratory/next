<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Aop\Contract;

use Closure;
use Max\Aop\JoinPoint;

interface AspectInterface
{
    public function process(JoinPoint $joinPoint, Closure $next): mixed;
}
