<br>

<p align="center">
<img src="https://raw.githubusercontent.com/topyao/max/master/public/favicon.ico" width="120" alt="Max">
</p>

<p align="center">轻量 • 简单 • 快速</p>

<p align="center">
<img src="https://img.shields.io/badge/php-%3E%3D7.4-brightgreen">
<img src="https://img.shields.io/badge/license-apache%202-blue">
</p>

> database

> redis

Max框架Redis组件

# 如何使用

## 安装

```shell
composer require max/database:dev-master
```

## 配置文件示例

```php

/**
 * redis.php
 */
<?php

return [
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
];
```

* host 可以是字符串或者数组，字符串或者只有一个host的时候表示只有一台Redis，读写分离不会触发，数组为多台Redis，会触发读写分离
* port / auth 可以是字符串或者数组，字符串表示所有主机共用同一套配置，否则每一台都应该设置对应的值，数组的键应该和host保持对应。
* master 主服务器，负责写入，例如一些操作`set`,`hSet`
  会使用主服务器，这个参数要求是一个数组，数组中的数字表示host配置中的host的键，这里主服务器有4台，会随机分配，可以重复某一个值来调整权重。例如'master' => [0, 1, 1, 1, 1, 1, 4, 5]
  ,这样的话分配给1号redis服务器的概率是5/8,而其他三台的概率均为1/8。
* slave 从服务器，负责读取。配置类似master

> 所有配置均可以设置为字符串或者数组两种形式

## 代码示例

```php
$config = include 'redis.php';
$redis  = new Max\Database\Redis($config);
$redis->set('t', 'test');
$redis->get('t');
```

# 欢迎参与开发

> 官网：https://www.1kmb.com
