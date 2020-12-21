<?php

if (is_file($_SERVER["DOCUMENT_ROOT"] . $_SERVER["SCRIPT_NAME"])) {
    return false;
} else {
    define('ROOT', getcwd() . '/');
    require ROOT . 'vendor/autoload.php';
    //调用框架初始化方法
    Yao\App::run();
}
