<?php

namespace Max\HttpMessage\Bags;

use Max\Http\Cookie;

class CookieBag
{
    /**
     * @var array
     */
    protected array $cookies = [];

    /**
     * @param Cookie $cookie
     */
    public function add(Cookie $cookie)
    {
        $this->cookies[] = $cookie;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->cookies;
    }
}
