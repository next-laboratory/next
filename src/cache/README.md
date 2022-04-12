<br>

<p align="center">
<img src="https://raw.githubusercontent.com/topyao/max/master/public/favicon.ico" width="120" alt="Max">
</p>

<p align="center">轻量 • 简单 • 快速</p>

<p align="center">
<img src="https://img.shields.io/badge/php-%3E%3D7.4-brightgreen">
<img src="https://img.shields.io/badge/license-apache%202-blue">
</p>

# 起步

缓存组件基于PSR16开发，已经独立，不再必须使用MaxPHP。你可以使用下面的命令安装开发版本。

```
composer require max/cache:dev-master
```

# 使用

## 如果你在使用MaxPHP, 你可以直接使用依赖注入的方式使用缓存

```php
//依赖注入
pubilc function index(\Max\Cache\Cache $cache){
    $cache->get('stat');
}
```

## 如果你没有使用MaxPHP，可以按照下面的方式使用

如果你使用文件缓存，安装好后你可能需要修改配置中的缓存存放路径，参考代码

```php
<?php

use Max\Cache\Cache;

require './vendor/autoload.php';
//配置文件
$config = include './vendor/max/cache/src/cache.php';
$cache = new \Max\Cache\Cache($config)
//如果需要切换存储，只需要将参数传递给store方法
$cache = $cache->store('store');
//设置缓存
$cache->set('stat', 12, 10);
//读取缓存
var_dump($cache->get('stat'));

```

# 配置文件

文件内容如下：

```php
<?php

return [
    'default'  => 'file',
    'handlers' => [
        //文件缓存
        'file'      => [
            'handler' => \Max\Cache\Handlers\File::class,
            'options' => [
                'path' => env('cache_path') . 'app',
            ],
        ],
        // redis缓存
        'redis'     => [
            'handler' => \Max\Cache\Handlers\Redis::class,
            'options' => [
                //所有Redis的host[不区分主从]
                'host'   => [
                    '127.0.0.1',
                    '127.0.0.1',
                    '127.0.0.1',
                    '127.0.0.1',
                    '127.0.0.1',
                    '127.0.0.1',
                    '127.0.0.1',
                ],
                //端口 string / array
                'port'   => 6379,
                //密码 string / array
                'auth'   => '',
                //主Redis ID [host中主机对应数组的键]
                'master' => [0, 1, 4, 5],
                //从Redis ID [host中主机对应数组的键]
                'slave'  => [2, 3, 6]
            ],
        ],
        //memcached缓存
        'memcached' => [
            'handler' => \Max\Cache\Handlers\Memcached::class,
            'options' => [
                'host' => '127.0.0.1', //主机
                'port' => 11211        //端口
            ],
        ]
    ],
];

```
> 官网：https://www.1kmb.com
