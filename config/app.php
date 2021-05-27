<?php

return [
    //是否开启调试
    'debug'            => env('app.debug', true),
    //是否记录日志
    'log'              => env('app.log', true),
    //自动开启session
    'auto_start'       => env('app.auto_start', false),
    //默认时区
    'default_timezone' => env('app.default_timezone', 'PRC'),
    //参数过滤
    'filter'           => ['trim', 'htmlspecialchars'],
    //默认语言
    'language'         => 'zh',
    //异常模板
    'exception_view'   => '',
    //类别名
    'alias'            => [],
    //全局中间件
    'middleware'       => [
//        \App\Http\Middleware\BasicAuth::class
//        \Max\Http\Middleware\GlobalCross::class,
    ],
    //服务提供者
    'provider'         => [
        'http' => [
            \Max\DatabaseService::class,
            \Max\CacheService::class,
            \Max\ViewService::class,
            \Max\ValidatorService::class
        ],

        'cli' => [
            \Max\Console\ConsoleService::class,
        ],
    ],
    //响应Header中的Powered-By
    'powered_by'       => 'MaxPHP'
];
