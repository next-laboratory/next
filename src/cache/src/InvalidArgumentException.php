<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Cache;

use InvalidArgumentException as InvalidArgument;
use Psr\SimpleCache\InvalidArgumentException as PsrCacheInvalidArgument;

class InvalidArgumentException extends InvalidArgument implements PsrCacheInvalidArgument {}
