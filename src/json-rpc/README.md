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

#### 处理Http请求

```php
<?php

namespace App\Http\Controller;

use Max\Di\Attribute\Inject;
use Max\JsonRpc\Server;
use Max\Routing\Attribute\Controller;
use Max\Routing\Attribute\GetMapping;
use Psr\Http\Message\ServerRequestInterface;

#[Controller(prefix: 'jsonrpc')]
class JsonRpcController
{
    #[Inject]
    protected Server $server;

    #[GetMapping(path: '/')]
    public function handle(ServerRequestInterface $request)
    {
        return $this->server->serveHttp($request);
    }
}
```

如上新建了一个控制器，路由地址为/jsonrpc，启动服务

> 服务端必须使用swoole/workerman等常驻内存的环境

### 测试请求

> GET 127.0.0.1:8989/jsonrpc
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
