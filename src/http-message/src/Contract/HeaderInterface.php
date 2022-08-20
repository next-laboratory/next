<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Http\Message\Contract;

interface HeaderInterface
{
    public const HEADER_CONTENT_TYPE                     = 'Content-Type';

    public const HEADER_SET_COOKIE                       = 'Set-Cookie';

    public const HEADER_PRAGMA                           = 'Pragma';

    public const HEADER_ACCEPT                           = 'Accept';

    public const HEADER_EXPIRES                          = 'Expires';

    public const HEADER_CACHE_CONTROL                    = 'Cache-Control';

    public const HEADER_CONTENT_TRANSFER_ENCODING        = 'Content-Transfer-Encoding';

    public const HEADER_CONTENT_DISPOSITION              = 'Content-Disposition';

    public const HEADER_ORIGIN                           = 'Origin';

    public const HEADER_ACCESS_CONTROL_ALLOW_ORIGIN      = 'Access-Control-Allow-Origin';

    public const HEADER_ACCESS_CONTROL_MAX_AGE           = 'Access-Control-Max-Age';

    public const HEADER_ACCESS_CONTROL_ALLOW_CREDENTIALS = 'Access-Control-Allow-Credentials';

    public const HEADER_ACCESS_CONTROL_ALLOW_METHODS     = 'Access-Control-Allow-Methods';

    public const HEADER_ACCESS_CONTROL_ALLOW_HEADERS     = 'Access-Control-Allow-Headers';
}
