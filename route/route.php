<?php

/**
 * 路由定义文件
 */

use \Yao\Facade\Route;

Route::get('/', [\app\index\Controller\Index::class, 'list'])->alias('index');

Route::get('index', 'index@index/index')->alias('index');
