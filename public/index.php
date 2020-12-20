<?php

define('ROOT', dirname(getcwd()) . '/');
require ROOT . 'vendor/autoload.php';
//调用框架初始化方法
yao\App::run();
