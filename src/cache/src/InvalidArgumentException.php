<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Cache;

use InvalidArgumentException as InvalidArgument;
use Psr\SimpleCache\InvalidArgumentException as PsrCacheInvalidArgument;

class InvalidArgumentException extends InvalidArgument implements PsrCacheInvalidArgument
{
}
