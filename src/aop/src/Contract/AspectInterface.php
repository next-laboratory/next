<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Aop\Contract;

use Next\Aop\ProceedingJoinPoint;

interface AspectInterface
{
    public function process(ProceedingJoinPoint $joinPoint);
}
