<br>

<p align="center">
<img src="https://raw.githubusercontent.com/marxphp/max/master/public/favicon.ico" width="120" alt="Max">
</p>

<p align="center">轻量 • 简单 • 快速</p>

<p align="center">
<img src="https://img.shields.io/badge/php-%3E%3D8.0-brightgreen">
<img src="https://img.shields.io/badge/license-apache%202-blue">
</p>

# 起步

缓存组件基于PSR16开发，已经独立，不再必须使用MaxPHP。你可以使用下面的命令安装开发版本。

```
composer require max/cache:dev-master
```

# 使用

> 如果你使用文件缓存，安装好后你可能需要修改配置中的缓存存放路径，参考代码

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
> 官网：https://www.1kmb.com
