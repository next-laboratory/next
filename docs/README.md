> MaxPHP是一款基于Swoole的常驻内存的PHP开发框架。目前处于开发阶段，性能测试如下

测试环境

```
Intel(R) Xeon(R) Platinum 8255C CPU @ 2.50GHz
CPU(s):                2
CPU MHz:               2494.140
Memory:                4G

PHP 8.1.0 (cli) (built: Dec 10 2021 07:51:24) (NTS)
Copyright (c) The PHP Group
Zend Engine v4.1.0, Copyright (c) Zend Technologies
    with Zend OPcache v8.1.0, Copyright (c), by Zend Technologies

Swoole                 4.8.5
```

测试结果

```
Server Software:        swoole-http-server
Server Hostname:        127.0.0.1
Server Port:            8088

Document Path:          /
Document Length:        934 bytes

Concurrency Level:      100
Time taken for tests:   0.138 seconds
Complete requests:      1000
Failed requests:        0
Write errors:           0
Total transferred:      1098000 bytes
HTML transferred:       934000 bytes
Requests per second:    7247.58 [#/sec] (mean)
Time per request:       13.798 [ms] (mean)
Time per request:       0.138 [ms] (mean, across all concurrent requests)
Transfer rate:          7771.34 [Kbytes/sec] received

Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:        0    2   1.6      2       8
Processing:     2   11   3.6     11      22
Waiting:        0   10   3.5      9      21
Total:          5   13   3.3     13      24

Percentage of the requests served within a certain time (ms)
  50%     13
  66%     14
  75%     15
  80%     16
  90%     17
  95%     19
  98%     21
  99%     23
 100%     24 (longest request)
```

# 特性

一、 组件和框架核心分离
二、 基于 Psr7 的 HTTP-Message
三、 基于 Psr11 的容器，支持接口注入，AOP [支持注解]
四、 Max/Database[基于Swoole连接池的数据库操作组件，支持 MySQL、PostgreSQL，Redis 等]
五、 基于 Psr14 的事件[支持注解]
六、 基于 Psr15 的中间件[支持注解]
七、 基于 Psr16 的缓存组件,支持 File,Memcached,Redis,APC[可扩展]
八、 支持路由功能[支持注解]
九、 Blade 视图引擎
十、 命令行，验证器等

# 安装

### 要求

```
PHP >= 8.0
Swoole >= 4.6
```

> 如果你没有使用过composer 可以先了解一下这块的知识 -> [学习笔记](https://www.1kmb.com/note/128.html)

### 使用Composer安装

```
composer create-project max/swoole-project:dev-master # 新建http项目
```

这行命令会在你命令执行目录安装框架，使用下面的命令启动服务

```
php bin/max swoole start
```

框架强制路由，框架对数据类型比较敏感，例如在该设置为`true`时候不要设置`1`。否则会报错。

### Nginx代理

```
server
{
    listen 80;
    server_name www.maxphp.com;
    index index.php index.html index.htm default.php default.htm default.html;
    root /www/wwwroot/maxphp/public;
    location / {
      proxy_set_header Host $host;
      proxy_set_header X-Real-IP $remote_addr;
      proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
      if (!-f $request_filename ) {
        proxy_pass http://127.0.0.1:8080;
      }
    }
}
```

# 目录结构

## 结构

- app 应用目录

    - Controllers 控制器目录
    - Events 事件
    - Listeners 监听器
    - Exceptions 异常相关
    - Middlewares 中间件目录
    - Jobs 队列任务
    - Resources 资源
    - Model 模型目录
    - helpers.php 辅助函数
    - Kernel.php http内核
- bin
    - max.php 启动文件
- config 配置文件目录
    - app.php 应用配置文件
    - cache.php 缓存配置文件
    - logger.php 日志配置文件
    - redis.php Redis配置文件
    - queue.php 队列配置文件
    - server.php 服务配置文件
    - session.php session配置文件
    - database.php 数据库配置文件
    - view.php 视图配置文件
- public 静态资源目录
- runtime 运行时文件（日志，缓存，代理类等）
- vendor 依赖（包含框架核心）
- views 视图目录
- .env 环境变量文件
- .example.env 环境变量示例文件
- composer.json composer配置文件
- composer.lock composer锁定文件
- LICENSE 开源许可证
- README.md 手册

# 配置

配置文件包含两种，一种是`config`目录下的以小写字母开头的`.php`为后缀的文件，另一种是框架根目录下的`.env`文件，下面简单介绍下如何使用他们。

## ENV

在开发环境下大多数配置都可以通过`.env`文件获取，而且默认为从`.env`文件获取，线上环境需要删除`.env`文件或者将配置中的`env`去掉，例如在`app.php`中有这样`'debug' => env('app.debug', false),`一个配置，我们可以更改为`'debug' => false,` `.env`文件可以使用节，例如：

```
[APP]
DEBUG=true #开启调试AUTO_START=true
```

其中`app`就是一节，获取`DEBUG`配置可以使用`env('app.debug')`

## Config

配置文件是位于`config`目录下，以`.php`结尾的返回一个关联数组的文件。

获取所有配置使用

```php
config(string $key, mixed $default = null): mixed
```

可以传入一个参数例如`app`,则会获取`app.php`文件中的配置，传入`app.auto_start` 则获取`app`中的`auto_start`参数的值

如果需要自定义一个配置文件，可以在`/config`目录下新建例如`alipay.php`文件并返回一个数组。

```php
config('alipay.param');       //获取配置
```

### 使用注解

如果你的实例是使用容器实例化的，那么可以使用注解来注入配置

```php
class User {
	/**
     * @var string
     */
    #[\Max\Config\Annotations\Config(key: 'qcloud.user.secret_key', default = '123')]
    protected string $secretKey;
}
```

你不需要在构造方法中初始化该属性，容器会根据注解注入该值，此时`$secretKey`的值便是配置文件中的`qcloud.user.secret_key`，如果配置文件中该值不存在，则会是该注解的`default`参数对应的值

# DI

## 容器

容器主要负责一些单例的依赖注入，并且在生命周期中容器内的实例应该保持不变。

> 这里的容器主要作用有四个，依赖查找，依赖注入，属性注入，回调，存放在容器中的实例一般是单例，在开发时应该注意哪些实例从容器中获取，哪些从上下文获取

### 获取容器实例

```php
\Max\Di\Context::getContainer();
container();
```

### 绑定标识

```
container()->alias($id, $className);
```

以后使用容器的`API`就不需要传入完整类名

### 实例化类

> 使用容器实例化类

```
$obj = make($classNameOrId, $arguments = []);
```

第一个参数传入一个完整类名或者已经绑定的标识，第二个参数是传递给类构造方法的参数列表数组，该方法会将实例保存在容器中，如果不想将实例保存在容器中，需要使用`resolve`方法

```php
$obj = resolve($classNameOrId, $arguments = []);
```

注意：如果你使用`resolve`,但是类的构造函数依赖其他实例，那么该依赖会被保存到容器

### 方法调用

> 使用容器调用实例的方法

```
call([$className,$method],$arguments = []);
```

第一个参数为一个数组，数组的第一个元素为需要实例化的类名，第二个元素为要调用的方法名。第二个参数为给方法传递的参数列表, 默认会将实例保存在容器中，如果不想保存，可以将`$className`传入一个对象

### 闭包的依赖注入

```
container()->callFunc(function(\Max\Http\Request $request){    
	// Do something.
}, array $arguments);
```

### 获取实例

> 获取容器内的实例可以使用`get`方法

```
container()->get($className);
```

> 获取实例可以使用还可以使用`make`方法,`container`实例的对象属性访问方式，`container`实例的属性数组访问方式。

### 判断存在与否

> 判断容器中的实例是否存在可以使用

```
container()->has($abstract);
```

> 注意：控制器方法是始终实现依赖注入的

### 参数解析解释

> 因为容器提供的方法make/resolve/call/callFunc可以传递用户自定义参数，因此有必要对参数的解析做下说明

```
class Test {
	public function __construct(ServerRequestInterface $serverRequest, \Closure $arg1, $arg2, $arg3 = 1) {
		// 
	}
}
// 不含参数，此时arg1,arg2会被容器传递null值，会报错，因为参数arg1是闭包，但是传递的参数中没有符合的，所以会传递null，导致类型不匹配，arg3会被传递默认值1
$test1 = make(Test::class);
// 含参数，会报错，报错同test1，如果构造函数中不含闭包，则自定义的未命名参数会按照参数顺序传递给构造函数
$test2 = make(Test::class, [123]);                            
// 含命名参数，容器会根据方法需要的参数名，从命名参数中查找对应的值进行注入，此时传递给Test构造方法的参数列表为$serverRequest实例， arg1闭包以及arg2的值，arg3为默认值
$test3 = make(Test::class, ['arg1' => fn() => 1, 'arg2' => 1] 
```

> 综上，建议使用关联数组来传递参数，以方便容器识别和对应

## 注解

### 使用

目前支持使用注解定义中间件，注入实例，注入配置

```php
use \Max\Config\Annotations\Config;
use \Max\Di\Annotation\Inject;
use Psr\Http\Message\ServerRequestInterface;

// 注入配置
#[Config(key: 'app', default: [])]
protected $appConfig;

// 注入实例
#[Inject(id: ServerRequestInterface::class)]
protected $request;

#[Inject()]
protected \Max\Contracts\ContainerInterface $container;
```

> 注意： 使用容器实例化的类的属性均可以使用注解注入

### 自定义注解[属性注解]

自定义的注解需要实现`\Max\Di\Contracts\PropertyAttribute` 接口中的handle方法，该方法第一个参数为`ReflectionClass`，第二个参数为当前属性的`ReflectionProperty`，第三个参数为需要注入到的实例， 例如创建下面的注解类

```php
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Env implements \Max\Di\Contracts\PropertyAttribute {
    protected $key;
    
    public function handle(ReflectionClass $reflectionClass, \ReflectionProperty $property,object $object){
       Context::getContainer()->setValue($object, $property, env($key));
    }
}

```

这样你就可以在属性上方添加原生注解，例如

```php
#[Env(key: 'app.debug')]
protected $debug;
```

容器会自动给属性注入需要的值

## 其他自定义注解

### 定义一个收集器

```
class Collector implements \Max\Di\Contracts\CollectorInterface
{
	public static function collectClass(string $class, object $attribute): void
    {
    }

    public static function collectMethod(string $class, string $method, object $attribute): void
    {
    }

    public static function collectProperty(string $class, string $property, object $attribute): void
    {
    }
}
```

你可以自定义收集行为，如果你只需要其中某个方法，可以继承AbstractCollector类。

### 注册收集器

在/bin/max.php的以下代码中第二个参数加入收集器类，当扫描到注解后会调用对应收集器的方法

```
Scanner::init($loader, [ListenerCollector::class, RouteCollector::class], $repository->get('di.scanDir'), BASE_PATH . 'runtime');
```

### 示例

```php
interface ValidationAttribute {
	
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class ValidateRuleMax {
	public function __construct(public function $max) {
		
	}
}

class ValidationCollector extends AbstractCollector
{
	protected array $container = [];

    public static function collectProperty(string $class, string $property, object $attribute): void
    {
    	if(self::isValid($attribute)) {
    		self::$container[$class][$property][] = $attribute;
    	}
    }
    
    public static function getClassPropertyAttributes(string $class) {
    	return self::$container[$class] ?? [];
    }
    
    public static function isValid(object $attribute): bool {
    	return $attribute instanceof ValidationAttribute;
    }
}

class DoSomething {
	
	protected $a = '1212';
	
	public function do() {
		$properties = ValidationCollector::getClassPropertyAttributes(__CLASS__);
    	$len = $properties['a']->max;
        if(mb_strlen($this->a > $len)) {
        	throw new InvalidArgumentException('Length is invalid.');
        }
	}
}
```

## 切面

### 创建切面类

```php
<?php

namespace App\Aspects;

use Closure;
use Max\Di\Aop\JoinPoint;
use Max\Di\Contracts\AspectInterface;
use Max\Di\Exceptions\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\SimpleCache\CacheInterface;
use ReflectionException as ReflectionExceptionAlias;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Cacheable implements AspectInterface
{
    /**
     * @var CacheInterface|mixed
     */
    protected CacheInterface $cache;

    /**
     * @param int $ttl
     * @param string|null $key
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws ReflectionExceptionAlias
     */
    public function __construct(protected int $ttl = 0, protected ?string $key = null)
    {
        $this->cache = make(CacheInterface::class);
    }

    /**
     * @param JoinPoint $joinPoint
     * @param Closure $next
     * @return mixed
     */
    public function process(JoinPoint $joinPoint, Closure $next)
    {
        $key = $this->key ?? serialize([$joinPoint->getProxy()::class, $joinPoint->getMethod(), $joinPoint->getArguments()]);
        return $this->cache->remember($key, function () use ($next, $joinPoint) {
            return $next($joinPoint);
        }, $this->ttl);
    }
}
```

> 注意这是一个注解类，使用方法和laravel中间件如出一辙，注意如果使用Cacheable切面，控制器方法切勿返回ResponseInterface响应，而是直接返回数组或者字符串，这个问题在后面会解决

### 添加注解

```php
class IndexController 
{	
	#[Cacheable(ttl: 100)]
	public function index() {
		return ['test'];
	}
}
```

如上所示，控制器index方法响应的内容会被缓存100秒，只要被Scanner扫描且代理的类均可以使用切面，并且对于new关键字实例化的对象也可以切入。

# 路由

## 路由定义

> 路由定义支持注解，注解扫描路径在`app/Http/Kernel.php`中的`routeScanDir`数组中配置

```php
#[Controller(prefix: 'index', middleware: [BasicAuthentication::class])]
class Index
{
    #[GetMapping(path: '/user/<id>.html', domain: '*.1kmb.com')]
    public function index()
    {
        echo 'patch';
    }

}
```

上面的代码定义了一个 Index 控制器，并使用 Controller 注解设置了路由的前缀为 index, 该控制器中全部方法的中间件为`BasicAuthentication::class`， 并且使用`GetMapping`
注解定义了一个路由，`path`为`/user/<id>.html`， 那么实际请求的地址可以为`/index/user/1.html`，注意在该路由中还注册了对应的域名`*.1kmb.com` 表示该方法仅能被该泛域名访问到, 支持的注解如下，分别对应了不同的请求方法，其中RequestMapping对应的请求方法默认为`GET`，`POST`，`HEAD`，可使用`method`参数来自定义

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

> 控制器方法会使用容器来调用，因此控制器是单例，且由容器调用的方法均实现依赖注入，注解查找等特性

```
class IntexController {
	public function __construct(public ServerRequestInterface $serverRequest) {
		//
	}
	
	#[GetMapping(path: '/<id>')]
	public functin index(CacheInterface $cache, $id) {
		return ['test'];
	}
}
```

控制器方法可以返回多种类型的值，例如`ResponseInterface`，`Arrayable`,` JsonSerializable`，数组，标量等，对于`ResponseInterface`，框架会直接响应；对于`Arrayable`，`JsonSerializable`会转换为数组和数组类型一致，最终转换为`Json`响应；对于其他标量，框架会转换为`Html`响应

# 请求

> 请使用`Max\Http\ServerRequest`类，是实现了`Psr7 ServerRequest`的请求类，如果你使用依赖注入的方式，那么可以直接提示类型为`Psr7 ServerRequestInterface`

## 请求头

```php
\Max\Http\ServerRequest::header($name): string
\Max\Http\ServerRequest::getHeaderLine($name): string
```

上面两个方法会返回请求头字符串，`header` 方法返回值 `getHeaderLine` 是一样的

## Server

```php
\Max\Http\ServerRequest::server($name): string     // 一条
\Max\Http\ServerRequest::getServerParams(): array // 全部
```

获取`$_SERVER`中的值

## 判断请求方法

```php
\Max\Http\ServerRequest::isMethod($method): bool
```

不区分大小写的方式判断请求方式是否一致

## 请求地址

```php
\Max\Http\ServerRequest::url(bool $full = false): string
```

返回请求的地址，`$full`为`true`，则返回完整地址

## Cookie

```php
\Max\Http\ServerRequest::cookie(string $name): string   // 单条
\Max\Http\ServerRequest::getCookieParams(): array       // 全部
```

获取请求的Cookie，一般也可以直接从`Header`中获取

## Ajax

```php
\Max\Http\ServerRequest::isAjax(): bool
```

判断当前请求是否是`Ajax`请求, 注意：有部分前端框架在发送Ajax请求的时候并没有发送X_REQUESTED_WITH头，所以这个方法会返回false

## 判断path


```php
\Max\Http\ServerRequest::is(string $path): bool
```

判断当前请求的`path`是否和给定的`path`匹配，支持正则

## 获取输入参数


```php
\Max\Http\ServerRequest::get($key = null, $default = null)                         // $_GET
\Max\Http\ServerRequest::post($key = null, $default = null)                        // $_POST
\Max\Http\ServerRequest::all()                                                     // $_GET + $_POST
\Max\Http\ServerRequest::input($key = null, $default = null, ?array $from = null)  // $_GET + $_POST
```

获取请求的参数，这些参数是通过PHP全局变量加载进来的，当$key为null的时候会返回全部参数，如果为字符串会返回单个，如果不存在返回default，如果$key是数组，则会返回多个参数，$default此时可以为数组，数组的键为参数键，数组的值为参数的默认值

例如

```php
\Max\Http\ServerRequest::get('a');
```

可以给第二个参数传入一个默认值，例如

```php
\Max\Http\ServerRequest::get('a','default');
```

获取多个参数可以使用

```php
\Max\Http\ServerRequest::get(['a','b']);
```

可以传入一个关联数组，数组的键为参数名，值为默认值，例如

```php
\Max\Http\ServerRequest::get(['a', 'b'], ['a' => 1]);
```

此时如果`a`不存在，则`a`的值为`1`

## 文件

```php
\Max\Http\ServerRequest::file($name);
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

# 响应

> 请使用`Max\Http\Response` 类或者接口注入`ResponseInterface`，该类实现了`Psr7` 的接口，控制器方法必须返回`ResponseInterface`

## 字符串响应

```php
\Max\Http\Response::html(string $html);
```

## json响应

```php
\Max\Http\Response::json(array $jsonable);
```

## Code

```php
\Max\Http\Response::html()->withStatus(int $code);
\Max\Http\Response::html()->code(int $code);
```

## Header

```php
\Max\Http\Response::html()->withAddedHeader($name, $value);       // 如果之前没有，则会新增，如果有则会追加
\Max\Http\Response::html()->withHeader($name, $value);            // 如果之前有，则之前的会被清空
\Max\Http\Response::html()->header($name, $value);                // 同withAddedHeader
```

## Body

```php
\Max\Http\Response::withBody(StreamInterface $body);                // Body
```

## 其他响应[待补充]

例如给浏览器输出一张图片

# Session

> Session可以使用`File`, `Cache`, 驱动，`File` 驱动参考了`ThinkPHP`， 感谢。 如果使用`Cache`, 则`Cache`请不要使用文件驱动，使用`Session`请使用`\Max\Http\Session`

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

# 视图

一款可扩展的视图适配器，内置Blade视图引擎，可独立使用

## 安装

```
composer require max/view
```

## 使用

> Blade 可能会有未知的Bug，使用时需要注意，Blade引擎支持的语法如下

- {{}}
- {{-- --}}
- {!! !!}
- @extends
- @yield
- @php
- @include
- @if
- @unless
- @empty
- @isset
- @foreach
- @for
- @switch
- @section

> 如果使用`extends` + `yield` + `section`, 务必保证子模板中除了`extends` 之外的所有代码均被`section` 包裹

## 配置文件

安装完成后框架会自动将配置文件`view.php`移动到根包的`config`目录下，如果创建失败，可以手动创建。

文件内容如下：

```php
<?php

return [
    'engine'  => '\Max\View\Engines\Blade',
    'options' => [
        // 模板目录
        'path'        => __DIR__ . '/../views/',
        // 编译和缓存目录
        'compile_dir' => __DIR__ . '/../storage/cache/views/compile',
        // 模板缓存
        'cache'       => false,
        // 模板后缀
        'suffix'      => '.blade.php',
    ],
];


```

## 独立使用

```php
$config   = require '../view.php'; 
$renderer = new \Max\View\Renderer($config);
$user = new User();
$renderer->render('index', ['user' => $user]);
```

## 集成

可以直接注入`\Max\Foundation\Renderer`来使用，或者按照下面的方式使用

```php
$engine = config('view.engine');
$renderer = new \Max\View\Renderer(new $engine(config('view.options')));
return $renderer->render('index', ['test' => ['123']]);
```

## 自定义引擎

自定义引擎必须实现`ViewEngineInterface`接口, 将新的引擎实例传递给渲染器即可

# 事件

> 事件基于Psr-14实现，事件可独立使用

```shell
composer require max/event
```

## 事件监听器

需要创建一个Listener类并实现`\Max\Event\Contracts\EventListenerInterface` 中的`listen`和`process`方法。`listen`
方法要求返回一个数组，数组内的值为该事件监听器监听的事件， `process`方法要求传入一个事件对象，该方法不需要返回值，例如

```php
class UserStatusListener implements EventListenerInterface
{

    public function listen():array {
        return [
            \App\Events\UserRegistered::class,
        ];
    }

    public function process(object $event): void
    {
        $event->user = false;
    }

}
```

## 事件类

```php
class UserRegistered
{
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }
}
```

## 事件调度

```php
//实例化一个ListenerProvider, 构造函数需要传入所有监听器对象
$listenerProvider = new Max\Event\ListenerProvider(...[$listener1, $listener2]);
//实例化调度器，给构造函数传入listenerProvider实例
$dispatcher       = new \Max\Event\EventDispatcher($listenerProvider);

$user = User::find(1);

$event = $dispatcher->dispatch(new UserRegistered($user));
```

## 集成

> 如果你使用的是`MaxPHP`, 那么上面的大部分操作你都可以忽略

注册监听器可以直接使用注解，注解扫描路径在`config/app.php`中的`event.scanDir`中配置

```php
<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use Max\Event\Annotations\Listen;
use Max\Event\Contracts\EventListenerInterface;

#[Listen]
class UserStatusListener implements EventListenerInterface
{
    public function listen(): array
    {
        return [
            UserRegistered::class,
        ];
    }

    public function process(object $event): void
    {
        $event->user = false;
    }
}
```

### 使用

```php
    #[Inject]
    protected EventDispatcherInterface $eventDispatcher;

    public function index(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher->dispatch(new UserRegistered(['name' => 'max']));
        $eventDispatcher->dispatch(new UserRegistered(['name' => 'max']));
    }

```

> 注意事件调度器需要使用DI来管理，所以不要重新实例化，你可以使用注解或者类型提示注入调度器

# 缓存

存组件基于`Psr-16`开发，支持`file`，`memcached`，`redis`，`Apc`， 该组件可以独立使用

## 使用

你可以直接使用类型提示`\Max\Http\Cache::class`或者`CacheInterface` 来使用该类的实例的方法

```php
public function index(Max\Http\Cache $cache) {
    var_dump($cache->get('key'));
}
```

## 扩展功能

当`$key`缓存不存在会调用闭包并将闭包的返回值写入缓存并返回， `$ttl` 为缓存时间` (s)`

```php
Cache::remember($key, function() {
    return 'cache';
}, ?int $ttl = null)
```

自增

```php
Cache::incr($key, int $step = 1)
```

自减

```php
Cache::decr($key, int $step = 1)
```

> 自增自减step表示步长，如果缓存不存在，则从0开始增长

# 数据库

> 文档更新不及时，可能会有大量出入

## 安装

```
composer require max/database
```

> 支持mysql，pgsql

目前的`DB`类只对`mysql`支持良好，其他数据库暂时没有测试。如果有需求可以使用`composer`安装第三方的数据库操作类，例如：`medoo`，`thinkorm`

```php
class UserDao {
	#[Inject]
    protected \Max\Database\Query $query;
    
    public function get() {
    	return $this->query->table('users')->get();
    }
}
```

需要使用注入的方式注入`Max\Database\Query`类

## 新增

```
$query->table('users')->insert(['name' => 'username','age' => 28]);
```

## 删除

```
$query->table('users')->where('id', '10', '>')->delete();
```

删除id大于10的用户。

## 更新

```
$query->table('users')->where('id', '10', '>')->update(['name' => 'zhangsan']);
```

## 查询

### 查询构造器

主要有以下几个方法：`table`,`where`,`whereLike`,`whereExists`,`whereBetween`
,`whereNull`,`whereNotNull`,`order`,`group`,`join`,`leftJoin`,`rightJoin`,`limit`。

#### table

```
table(string $table)
```

如果有前缀则`$table`必须加上前缀

#### order

```
$query->table('users')->order(['id' => 'DESC','sex' => 'DESC'])->select();
```

最终的SQL可能是

```
SELECT * FROM users ORDER BY id DESC, sex DESC;
```

#### group

```php
$query->table('users')->group(['sex','id' => 'sex = 1'])->get();
```

最终的SQL可能是

```
SELECT * FROM users GROUP BY sex,id HAVING sex = 1;
```

#### limit

```php
$query->table('users')->limit(1,3)->get();
$query->table('users')->limit(1)->get();
```

根据数据库不同最终的SQL可能是

```
SELECT * FROM users LIMIT 3,1;
SELECT * FROM users LIMIT 1;
```

也可能是

```
SELECT * FROM users LIMIT 1 OFFSET 3;
```

#### join

联表有提供了三种方式`innerJoin``leftJoin``rightJoin`

例如如下语句：

```
$query->table('users')->join('books')->on('books.user_id', '=', 'users.id')->get();

$query->table('users')->leftJoin('books')->on('books.user_id', '=', 'users.id')->get();

$query->table('users')->rightJoin('books')->on('books.user_id', '=', 'users.id')->get();
```

最终的SQL可能是

```
SELECT * FROM users INNER JOIN books on books.user_id = users.id;
SELECT * FROM users LEFT JOIN books on books.user_id = users.id;
SELECT * FROM users RIGHT JOIN books on books.user_id = users.id;
```

#### where

例如我有如下的查询

```
$query->table('users')->where(['id' => 1, 'sex = 0'])->select();
$query->table('users')->where(['id' => 2], '>=')->select();
```

最终的SQL可能依次如下

```
SELECT * FROM users WHERE id = ? AND sex = 0;SELECT * FROM users WHERE id >= ?;
```

可以看到`id = ?` 和 `sex = 0` 说明id这个条件可以经过预处理的，而条件数组的键为数字的却不会被处理。

#### whereLike

例如我有如下的查询

```
$query->table('users')->whereLike(['username' => 1, 'sex = 0'])->select();
```

最终的SQL可能如下

```
SELECT * FROM users WHERE username LIKE ? AND sex = 0;
```

### 使用

#### 查询一条可以用get方法

> 查询users表中id为1的一条数据，通常要配合条件语句定位数据

```
$query->table('users')->where(['id' => 1])->get()
```

#### 查询某一个值

查询`users`表中的`id`为`2`的`username`

```
$query->table('users')->where(['id' => 2])->value('username');
```

#### 总数查询

查询`users`表的总数据，返回`int`

```
$query->table('users')->count();
```

> `count()`方法可以传入一个参数即列名，不传默认为*

#### 查询多条可用`select`方法

> 查询users表中id在1，2，3范围内的2条，其偏移量为3

```
$query->table('users')->field('id')->whereIn(['id' => [1,2,3]])->limit(2,3)->select();
```

查询到的是数据集对象，可以使用`toArray`或者`toJson`获取，例如

```
$query->table('users')->limit(1)->select()->toArray();
```

#### 查询某一列值

查询`users`表中的`username`列

```
$query->table('users')->column('username');
```

### 事务

```
$res = $query->transaction(function (Query $query, \PDO $pdo) {    
	//$pdo->setAttribute(\PDO::ATTR_ORACLE_NULLS,true); 可以自行设置需要的参数    
	$deletedUsers = $query->name('users')->whereIn(['id' => [1,2,3]])->delete();    
    if(0 == $deletedUsers){        
        throw new \PDOException('没有用户被删除!');    
    }    
    return $query->name('books')->whereIn(['user_id' => [1,2,3]])->delete();
});
```

其中`transaction`接受一个闭包的参数，该回调可以传入两个参数，一个是当前的查询实例，另一个是`PDO`实例，可以看到这里执行了两条`SQL`
，当执行结果不满足时可以手动抛出`PDOException`异常来回滚事务，否则如果执行过程抛出异常也会自动回滚事务。执行没有错误结束后提交事务。该闭包需要返回执行的结果，返回的执行结果会被`transaction`方法返回。

# 模型

> 模型的目录在`app\Models`下，新建模型继承`Max\Database\Eloquent\Model`类即可使用模型提供的方法。模型名为表名，也可以在模型中设置$table属性，此时的表名就是name的值。

例如我新建了一个Notes的模型，可以在模型中直接使用

```php
class Notes extends \Max\Database\Eloquent\Model 
{
    protected string $table = 'notes';
    
    protected array $fillable = [
        'title', 'text', 'publication_date' 
    ];
    
    protected array $cast = [
        'publication_date' => 'integer'
    ];
}
```

可以使用以下方法

```
User::query()->where()->get() // 使用query方法之后的操作和查询构造器一致
User::first();                // 返回第一个模型
User::find($id);                 // 返回单个User模型
User::get();                  // 返回全部User模型的集合
```

# 其他

## Redis

> redis已经继承到了Database中，可以直接注入Max\Database\Redis实例

```
public function index(Max\Database\Redis $redis) {
	$redis->get('key');
}
```

## 命令操作

使用`php max help`命令查看支持的命令

框架提供了不丰富的命令操作，目前支持路由列表，路由缓存，启动内置服务等操作；只需要在项目目录下打开命令窗口输入以下指令就可以方便地进行各种操作。

```shell
php max         //所有命令的帮助
php max server  //会启动服务
```
