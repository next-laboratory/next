一款兼容多环境的Redis组件包，支持Swoole连接池和普通连接，可以独立使用

## 安装

```shell
composer require max/redis
```

## 使用

```php
// 所有参数均通过connector传递
$redis = new \Max\Redis\Redis(new \Max\Redis\Connector\BaseConnector());
// 执行一条命令
$redis->exists('maxphp');
// 执行多条
$redis->wrap(function(Redis $redis) {
    if(!$redis->exists('maxphp')) {
        $redis->set('maxphp', 'good');
    } 
});
```

执行多条命令时需要使用wrap方法，方法接收一个接收\Redis实例的闭包，在闭包内通过调用\Redis实例的方法实现。
