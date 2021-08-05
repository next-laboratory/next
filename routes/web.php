<?php

/**
 * 路由定义文件
 */

use \Max\Facade\Route;

Route::get('/', 'App\Http\Controllers\Index@index')->alias('home')->cors('*');
