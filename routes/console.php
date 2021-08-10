<?php

/**
 *  指令注册
 */

use Max\Facade\Console;

Console::command('make', \App\Console\Commands\Make::class);
Console::command('route', \App\Console\Commands\Route::class);
Console::command('help', \App\Console\Commands\Help::class);
Console::command('help', \App\Console\Commands\Help::class);
Console::command('serve', \App\Console\Commands\Serve::class);
Console::command('vendor:publish', \App\Console\Commands\Vendor::class);

