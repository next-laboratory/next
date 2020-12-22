<?php

namespace App\Index\Middleware;

class Login
{
    public function handler($request, \Closure $next)
    {
        return $next($request);
    }
}
