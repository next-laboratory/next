<?php
declare(strict_types=1);

namespace Max\Cache\Exceptions;

use InvalidArgumentException as InvalidArgument;
use Psr\SimpleCache\InvalidArgumentException as PsrCacheInvalidArgument;

class InvalidArgumentException extends InvalidArgument implements PsrCacheInvalidArgument
{
}
