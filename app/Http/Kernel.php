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
        \App\Http\Middleware\AppDebug::class,
        \App\Http\Middleware\VariablesFilter::class,
//        \App\Http\Middleware\BasicAuthentication::class,
        \App\Http\Middleware\AllowCrossDomain::class,
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