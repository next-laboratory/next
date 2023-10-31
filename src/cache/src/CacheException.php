<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Cache;

use Psr\SimpleCache\CacheException as PsrCacheException;

class CacheException extends \Exception implements PsrCacheException
{
}
