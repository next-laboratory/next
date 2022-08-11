<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Cache\Exception;

use Exception;
use Psr\SimpleCache\CacheException as PsrCacheException;

class CacheException extends Exception implements PsrCacheException
{
}
