## json-rpc

## 安装

```shell
composer require max/json-rpc
```

## 服务端

### 添加注解收集器

在config/di.php的collectors中添加\Max\JsonRpc\ServiceCollector::class

### 新建service类

```php
<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace App\Service\JsonRpc;

use Max\JsonRpc\Attribute\RpcService;

#[RpcService(name: 'calc')]
class CalculateService
{
    public function sum(int $a, int $b): int
    {
        return $a + $b;
    }

    public function sub(int $a, int $b)
    {
        return $a - $b;
    }
}
```

上面的类使用RpcService注解来标识这是一个Rpc服务，名称为calc，其中两个方法sum和sub会被注册，调用时传递的方法名应该为calc.sum或者calc.sub

### 添加请求处理代码

> 如下为swoole环境下的代码，将代码放进onRequest事件回调中，后期会精简这部分的代码

```php
$psrRequest  = ServerRequest::createFromSwooleRequest($request);
$psrResponse = \Max\JsonRpc\Server::handle($psrRequest);
(new SwooleResponseEmitter())->emit($psrResponse, $response);
```

启动服务

### 测试请求

> GET 127.0.0.1:8989
```json
{
    "jsonrpc": "2.0",
    "id": 123,
    "method": "calc.sum",
    "params": {
        "a": 1,
        "b": 2
    }
}
```

响应

```json
{
    "jsonrpc": "2.0",
    "result": 3,
    "id": 123
}
```

## 客户端

请求

```php
$client   = new Client('http://127.0.0.1:8989');
$response = $client->call(new Request('calc.sum', ['a' => 1, 'b' => 2]));
dump($response);
```

响应

```
^ array:3 [
  "jsonrpc" => "2.0"
  "result" => 3
  "id" => "d562e03439af8a69d71ee833b8972bbd"
]
```

如果要发送通知可以使用notify方法
