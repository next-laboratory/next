<?php

namespace App\Http\Middleware;

class GlobalCross
{
    public function handle($request, \Closure $next)
    {
        $response = $next($request);
        return $response->withHeader('Access-Control-Allow-Origin', '*');
    }
}