<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Routing\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class GetMapping extends RequestMapping
{
    /**
     * @var array<int, string>
     */
    public array $methods = ['GET', 'HEAD'];
}
