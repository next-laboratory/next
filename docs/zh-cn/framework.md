# 路由

## 配置

框架提供了env和config两个函数，可以方便获取配置，如果你使用了AOP包，还可以直接使用注解将配置注入示例

```php
class User {
	/**
     * @var string
     */
    #[\Max\Config\Annotations\Config(key: 'qcloud.user.secret_key', default = '123')]
    protected string $secretKey;
}
```

如上secretKey将会被自动注入，如果配置文件中不存在，则默认123

## 路由定义

> 路由定义支持注解，注解扫描路径在`app/Http/Kernel.php`中的`routeScanDir`数组中配置

```php
#[Controller(prefix: 'index', middleware: [BasicAuthentication::class])]
class Index
{
    #[GetMapping(path: '/user/<id>.html', domain: '*.1kmb.com')]
    public function index(\Psr\Http\Message\ServerRequestInterface $request)
    {
        echo 'patch';
    }

}
```

上面的代码定义了一个 Index 控制器，并使用 Controller 注解设置了路由的前缀为 index, 该控制器中全部方法的中间件为`BasicAuthentication::class`， 并且使用`GetMapping`
注解定义了一个路由，`path`为`/user/<id>.html`， 那么实际请求的地址可以为`/index/user/1.html`，注意在该路由中还注册了对应的域名`*.1kmb.com` 表示该方法仅能被该泛域名访问到,
支持的注解如下，分别对应了不同的请求方法，其中RequestMapping对应的请求方法默认为`GET`，`POST`，`HEAD`，可使用`method`参数来自定义

- GetMapping
- PostMapping
- PutMapping
- DeleteMapping
- PatchMapping
- RequestMapping

> 如果你不习惯使用注解，可以在`app/Http/Kernel.php`的`map`方法中注册路由，例如

```php
protected function map(Router $router) {
	$router->group(function(Router $router) {
		$router->get('/', function(ServerRequestInterface $request) {
			return $request->all();
		});
		$router->group(function() {
			include_once 'routes.php';
		});
	});
}
```

如果你引入了其他文件，则可以在该文件中使用`$this`来访问`Router`对象, 例如文件`routes.php`中定义路由

```php
/** @var Router $this */
$this->get('/', [IndexController::class, 'index']);
```

# 控制器

```php
class IntexController {

	#[GetMapping(path: '/<id>')]
	public functin index(ServerRequestInterface $request, $id) {
		return ['test'];
	}
}
```

控制器是单例对象，和路由对应的方法支持依赖注入，并且参数名为`request`的参数会被注入当前请求类，该类不是单例，对于每个请求都是独立的。路由参数会被按照参数名注入，其他有类型提示的参数也会被注入

# 请求

> 请使用`Max\Http\Message\ServerRequest`类，是实现了`Psr7 ServerRequest`的请求类

## 请求头

```php
\Max\Http\Message\ServerRequest::header($name): string
\Max\Http\Message\ServerRequest::getHeaderLine($name): string
```

上面两个方法会返回请求头字符串，`header` 方法返回值 `getHeaderLine` 是一样的

## Server

```php
\Max\Http\Message\ServerRequest::server($name): string     // 一条
\Max\Http\Message\ServerRequest::getServerParams(): array // 全部
```

获取`$_SERVER`中的值

## 判断请求方法

```php
\Max\Http\Message\ServerRequest::isMethod($method): bool
```

不区分大小写的方式判断请求方式是否一致

## 请求地址

```php
\Max\Http\Message\ServerRequest::url(bool $full = false): string
```

返回请求的地址，`$full`为`true`，则返回完整地址

## Cookie

```php
\Max\Http\Message\ServerRequest::cookie(string $name): string   // 单条
\Max\Http\Message\ServerRequest::getCookieParams(): array       // 全部
```

获取请求的Cookie，一般也可以直接从`Header`中获取

## Ajax

```php
\Max\Http\Message\ServerRequest::isAjax(): bool
```

判断当前请求是否是`Ajax`请求, 注意：有部分前端框架在发送Ajax请求的时候并没有发送X_REQUESTED_WITH头，所以这个方法会返回false

## 判断path

```php
\Max\Http\Message\ServerRequest::is(string $path): bool
```

判断当前请求的`path`是否和给定的`path`匹配，支持正则

## 获取输入参数

```php
\Max\Http\Message\ServerRequest::get($key = null, $default = null)                         // $_GET
\Max\Http\Message\ServerRequest::post($key = null, $default = null)                        // $_POST
\Max\Http\Message\ServerRequest::all()                                                     // $_GET + $_POST
\Max\Http\Message\ServerRequest::input($key = null, $default = null, ?array $from = null)  // $_GET + $_POST
```

获取请求的参数，这些参数是通过PHP全局变量加载进来的，当$key为null的时候会返回全部参数，如果为字符串会返回单个，如果不存在返回default，如果$key是数组，则会返回多个参数，$default此时可以为数组，数组的键为参数键，数组的值为参数的默认值

例如

```php
\Max\Http\Message\ServerRequest::get('a');
```

可以给第二个参数传入一个默认值，例如

```php
\Max\Http\Message\ServerRequest::get('a','default');
```

获取多个参数可以使用

```php
\Max\Http\Message\ServerRequest::get(['a','b']);
```

可以传入一个关联数组，数组的键为参数名，值为默认值，例如

```php
\Max\Http\Message\ServerRequest::get(['a', 'b'], ['a' => 1]);
```

此时如果`a`不存在，则`a`的值为`1`

## 文件

```php
\Max\Http\Message\ServerRequest::file($name);
```

获取上传文件，这个方法暂时不建议使用

# 中间件

> 中间件基于`Psr15`实现，在`config/http.php` 中的`$middleware`数组中注册的为全局的中间件，均在路由匹配前执行， 其他中间件在路由匹配后执行

首先需要创建一个中间件，例如

```php
<?php
    
namespace App\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Login implement MiddlewareInterface
{ 
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface  
    {        
        // 前置操作
        $response = $handler->handle($request);
        // 后置操作     
        return $response;    
}
```

> 注意：上面代码中的view函数生成了Response响应，所以可以直接在中间件中返回

### 注解

```php
#[Controller(prefix: '/', middleware(TestMiddleware::class))]
class Index {
	#[
		GetMapping(path: '/'),
		Middleware(Test2Middleware::class)
	]
	public function index() {
		// Do something.
	}
}
```

上面的注解定义了两个中间件，控制器Index中的方法都注册了`TestMiddleware`中间件，`index`方法不仅包含`TestMiddleware`, 还包含`Test2Middleware`中间件。

# Session

> Session可以使用`File`, `Cache`, 驱动，`File` 驱动参考了`ThinkPHP`， 感谢。 如果使用`Cache`, 则`Cache`请不要使用文件驱动，使用`Session`
> 请使用`\Max\Http\Session`

Session配置文件如下

```php
<?php

return [
    'name'          => 'MAXPHP_SESSION_ID',
    'handler'       => [
        'class'   => '\Max\Session\Handlers\File',
        'options' => [
            'path' => env('storage_path') . 'session',
            'ttl' => 3600,
        ]
    ],
    'cookie_expire' => time() + 3600,
];

```

## 判断是否存在

```php
$session->has($name): bool
```

## 获取

```php
$session->get($name)
```

## 添加

```php
$session->set($name, $value): bool
```

可以是数组或者字符串

## 获取并删除

```php
$session->pull($name): bool
```

## 删除

```php
$session->remove($name): bool
```

## 销毁

```php
$session->destory(): bool
```

# 验证器

要使用验证器，需要安装验证器组件

```shell
composer require max/validator
```

## 使用

> 可以使用`Facade`或者实例化的方式使用验证器，暂时不推荐依赖注入

```php
$validator = new \Max\Validator\Validator();
$validator->make([
    'name' => 'maxphp',
], [
    'name' => 'required|max:10',
], [
    'name.required' => 'name是必须的',
    'name.max'      => 'name最大长度10',
])

// 验证失败了
if($validator->fails()){
    // 打印所有错误
    dd($validator->failed());
}
// 获取通过验证的字段列表
$data = $validator->valid();
```

上面的验证会验证所有的, 如果验证失败，你可以获取第一条错误

```php
$validator->errors()->first();
```

如果你需要在一旦出现验证失败就抛出异常

```php
$validator->setThrowable(true);
```
