<?php

namespace Max\Foundation;

//框架启动时间常量
define('APP_START_TIME', microtime(true));
//框架运行初始内存常量
define('APP_START_MEMORY_USAGE', memory_get_usage());

require __DIR__ . '/../vendor/autoload.php';

App::instance()->run();
