<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Routing\Attribute;

use Attribute;
use Max\Http\Message\Contract\RequestMethodInterface;

#[Attribute(Attribute::TARGET_METHOD)]
class PatchMapping extends RequestMapping
{
    /**
     * @var array<int, string>
     */
    public array $methods = [RequestMethodInterface::METHOD_PATCH];
}
