<?php

/**
 *  指令注册
 */

use Max\Facade\Console;

Console::command('make', \Max\Console\Commands\Make::class);