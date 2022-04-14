<?php
declare(strict_types=1);

namespace Max\Http;

use Exception;
use Max\Config\Repository;
use Max\Session\Session as MaxSession;

class Session extends MaxSession
{
    /**
     * @param Repository $repository
     *
     * @return MaxSession
     * @throws Exception
     */
    public static function __new(Repository $repository): MaxSession
    {
        return new static($repository->get('session'));
    }
}
