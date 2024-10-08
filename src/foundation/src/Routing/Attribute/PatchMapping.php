<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Foundation\Routing\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
class PatchMapping extends RequestMapping
{
    /**
     * @var array<int, string>
     */
    public array $methods = ['PATCH'];
}
