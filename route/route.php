<?php

/**
 * 路由定义文件
 */

use \Yao\Facade\Route;

Route::get('/', [\App\Index\Controller\Index::class, 'index']);
