<?php

/**
 * 路由定义文件
 */

use yao\facade\Route;

Route::get('/', [\app\index\controller\Index::class, 'index']);

Route::get('/index.html', [\app\index\controller\Index::class, 'index']);
