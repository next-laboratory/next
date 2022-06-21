多环境兼容的 Http Server

# 设计思想

> 全部符合psr规范

request -> kernel -> response

# 使用

需要新建类继承`\Max\Http\Server\Kernel`类

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
$kernel = \Max\Di\Context::getContainer->make(HttpKernel::class);

// 获取一个PsrServerRequest
$request = \Max\Http\Message\ServerRequest::createFromGlobals();

// 返回PsrResponse
$response = $kernel->through($request);

// 发送响应
(new \Max\Http\Server\ResponseEmitter\FPMResponseEmitter())->emit($response);

```

> 框架内置三种环境的ResponseEmitter，均可以自定义

# 示例

> FPM 环境

```php
(function() {
    $loader = require_once '../vendor/autoload.php';
    /** @var Kernel $kernel */
    $kernel   = Context::getContainer()->make(Kernel::class);
    $response = $kernel->through(ServerRequest::createFromGlobals());
    (new FPMResponseEmitter())->emit($response);
})();
```

你还可以通过继承Kernel类的方式来改写其中的某些方法或者放入全局中间件
