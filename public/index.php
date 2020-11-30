<?php

//框架开发阶段开启，线上一定要删掉或注释掉
ini_set('display_errors', 'on');
error_reporting(E_ALL);

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(getcwd()) . DS);
require ROOT . 'vendor/autoload.php';
//调用框架初始化方法
yao\App::run();
