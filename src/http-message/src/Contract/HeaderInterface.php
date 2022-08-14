<?php

namespace Max\Http\Message\Contract;

interface HeaderInterface
{
    public const HEADER_CONTENT_TYPE              = 'Content-Type';
    public const HEADER_SET_COOKIE                = 'Set-Cookie';
    public const HEADER_PRAGMA                    = 'Pragma';
    public const HEADER_EXPIRES                   = 'Expires';
    public const HEADER_CACHE_CONTROL             = 'Cache-Control';
    public const HEADER_CONTENT_TRANSFER_ENCODING = 'Content-Transfer-Encoding';
    public const HEADER_CONTENT_DISPOSITION       = 'Content-Disposition';
}
