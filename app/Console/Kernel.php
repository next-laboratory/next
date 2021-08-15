<?php

namespace App\Console;

use Max\Console;

class Kernel extends Console
{

    /**
     * 命令
     * @var array
     */
    protected $commands = [
        'make'           => Commands\Make::class,
        'route'          => Commands\Route::class,
        'help'           => Commands\Help::class,
        'serve'          => Commands\Serve::class,
        'vendor:publish' => Commands\Vendor::class,
    ];

    /*
     * 服务提供者
     * @var array
     */
    protected $providers = [

    ];
}







