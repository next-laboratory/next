<?php

namespace Next\Http\Server\Exception;

use Next\Http\Message\Exception\HttpException;

class MethodNotAllowedException extends HttpException
{
    protected $code = 405;
}
