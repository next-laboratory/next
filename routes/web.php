<?php

/**
 * 路由定义文件
 */

use \Max\Facade\Route;

Route::get('/', 'index@index')->alias('home')->cors('*');
