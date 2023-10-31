符合Psr7规范的兼容多容器的 Http Server

# 设计思路

request -> kernel -> response

# 使用

新建类继承`\Next\Http\Server\Kernel`类

```php
class HttpKernel extends Kernel 
{
    // 全局中间件
    protected array $middlewares = [];
    
    // 注册路由
    protected function map(Router $router)
    {
        $router->get('/', 'IndexController@index');
    }
}
```

然后使用容器实例化`HttpKernel`类

```php
$kernel = \Next\Di\Context::getContainer->make(HttpKernel::class);

// 获取一个PsrServerRequest
$request = \Next\Http\Message\ServerRequest::createFromGlobals();

// 返回PsrResponse
$response = $kernel->handle($request);

// 发送响应
(new \Next\Http\Server\ResponseEmitter\FPMResponseEmitter())->emit($response);

```

> 框架内置三种环境的ResponseEmitter，均可以自定义

# 示例

> FPM 环境

```php
(function() {
    $loader = require_once '../vendor/autoload.php';
    /** @var Kernel $kernel */
    $kernel   = Context::getContainer()->make(Kernel::class);
    $response = $kernel->handle(ServerRequest::createFromGlobals());
    (new FPMResponseEmitter())->emit($response);
})();
```

你还可以通过继承Kernel类的方式来改写其中的某些方法或者放入全局中间件
