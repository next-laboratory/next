<?php
declare(strict_types=1);

namespace Max\Http;

use Exception;
use Max\Config\Repository;

class Session extends \Max\Session\Session
{
    /**
     * @param Repository $repository
     *
     * @return \Max\Session\Session
     * @throws Exception
     */
    public static function __new(Repository $repository): \Max\Session\Session
    {
        return new static($repository->get('session'));
    }
}
