<?php

namespace App\Http;

use Max\Http;

class Kernel extends Http
{

    /**
     * 中间件
     * @var array
     */
    protected $middleware = [
        // take effect when app start
        'app'   => [
            \App\Http\Middleware\AppTrace::class,
            \App\Http\Middleware\VariablesFilter::class,
//            \App\Http\Middleware\BasicAuthentication::class,
            \App\Http\Middleware\AllowCrossDomain::class,
        ],
        // take effect after route matched
        'route' => [
        ],
    ];

    /**
     * 服务提供者
     * @var string[]
     */
    protected $providers = [
        \Max\DatabaseService::class,
        \Max\CacheService::class,
        \Max\ViewService::class,
        \Max\ValidatorService::class,
    ];

}