<?php

return [
    //是否开启调试
    'debug'             => env('app.debug', true),
    //是否记录日志
    'log'               => env('app.log', true),
    //默认时区
    'default_timezone'  => env('app.default_timezone', 'PRC'),
    //异常处理类
    'exception_handler' => \App\Http\Error::class,
    //类别名
    'aliases'           => [
        'console'  => \App\Console\Kernel::class,
        'http'     => \App\Http\Kernel::class,
        'log'      => \Max\Logger::class,
        'request'  => \Max\Http\Request::class,
        'route'    => \Max\Http\Router::class,
        'error'    => \Max\Error::class,
        'response' => \Max\Http\Response::class,
    ],
];
