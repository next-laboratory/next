<p align="center">
<img src="https://raw.githubusercontent.com/next-laboratory/next/master/public/favicon.ico" width="120" alt="Max">
</p>

<p align="center">轻量 • 简单 • 快速</p>

<p align="center">
<img src="https://img.shields.io/badge/php-%3E%3D8.0-brightgreen">
<img src="https://img.shields.io/badge/license-apache%202-blue">
</p>

# 起步

符合Psr16的缓存组件，支持File,Memcached,Redis,Apcu驱动。协程环境下需要自定义驱动

## 安装

```
composer require next/cache
```

## 使用

```php
<?php

use Next\Cache\Cache;

$cache = new \Next\Cache\Cache(new \Next\Cache\Driver\FileDriver('./runtime/cache'))
//设置缓存
$cache->set('stat', 12, 10);
//读取缓存
var_dump($cache->get('stat'));

```
