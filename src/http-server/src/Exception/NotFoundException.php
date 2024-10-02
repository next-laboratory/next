<?php

namespace Next\Http\Server\Exception;

use Next\Http\Message\Exception\HttpException;

class NotFoundException extends HttpException
{
    protected $code = 404;
}