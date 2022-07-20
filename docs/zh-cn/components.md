# 组件

## max/config

这是一款可以独立使用的组件包，可以使用点语法来获取加载的配置

### 安装

```shell
composer require max/config:dev-master
```

### 使用

```php
$repository = new \Max\Config\Repository();

// 扫描该路径下的所有PHP文件
$repository->scan([__DIR__]);

// 加载某一个文件
$repository->load([__DIR__.'./config/app.php']);

// 获取配置
$repository->get('app.debug');
```

> 注意：$repository示例应该保持单例，避免重复加载，加载配置的规则如下

例如对于app.php配置文件内容如下

```php
return [
    'debug' => true,
];
```

加载后会按照文件名作为外层数组的键，因此获取配置需要使用`$repository->get('app.debug'')`，支持使用点语法。

## max/di

一款简单的容器，可以独立使用

### 安装

```shell
composer require max/di:dev-master
```

### 使用

获取容器实例，注意不要直接实例化

```php
$container = \Max\Di\Context::getContainer();
$container = container();
```

绑定类和别名

```php
$container->bind(TestInterface::class, Test::class);
```

之后所有容器接口都可以使用TestInterface::class标识来获取Test::class实例

实例化对象, 不保存改对象, 但是会保存所有该类依赖的对象

```php
$container->resolve(Test::class);
```

实例化对象并存储

```php
$container->make(Test::class);
```

获取对象

```php
$container->get(Test::class);
```

调用方法

```php
$conatiner->call(callable $callable, array $arguments = []);
```

> 注意：所有需要传参的api均需要关联数组，数组的键为参数的名字。所有被容器实例化的类以及所有依赖都是单例

## max/routing

一款简单的可以独立使用的路由

### 安装

```shell
composer require max/routing
```

### 使用

```php

$router = new Router();

$router->get('index/{name}', function($name) {
    return $name;
});

// 路由分组示例
$router->prefix('api')
    ->middleware('class_name')
    ->pattterns(['id' => '\d+'])
    ->group(function(Router $router) {
        $router->get('/user/{id}', function($id = 0) {  
            var_dump('user');
        })->middlewares('auth');
        $router->middleware('user')->group(function() {
            //
        });
    });

// 解析路由，返回匹配到的Route对象
$route = $router->getRouteCollector()->resolve('GET', '/');
// 或者直接解析ServerRequestInterface对象
$route = $router->getRouteCollector()->resolveRequest($request);

var_dump($route);
```

此外还有一系列方法，例如

```php
$router->namespace('App\Http\Controllers')->get('/', 'Index@index');
```

- prefix
- middleware
- namespace
- patterns

## max/event

事件基于Psr-14实现，可以独立使用

### 安装

```shell
composer require max/event
```

### 使用

1. 需要创建一个`Listener`类并实现`\Max\Event\Contracts\EventListenerInterface` 中的`listen`和`process`方法。`listen`
   方法要求返回一个数组，数组内的值为该事件监听器监听的事件，
   `process`方法要求传入一个事件对象，该方法不需要返回值，例如

```php
class UserStatusListener implements EventListenerInterface
{
    /**
    * 返回该监听器监听的事件
    * @return string[]
    */
    public function listen():array {
        return [
            \App\Events\UserRegistered::class,
        ];
    }

    /**
    * 触发事件后的处理
    * @param object $event
    */
    public function process(object $event): void
    {
        $event->user = false;
    }
}
```

2. 需要创建一个`Event`类，例如

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

3. 实例化一个`ListenerProvider`, 构造函数需要传入所有监听器对象

```php
$listenerProvider = new ListenerProvider(...[new UserStatusListener]);
```

4. 实例化调度器，给构造函数传入`ListenerProvider`实例

```php
$dispatcher = new \Max\Event\EventDispatcher($listenerProvider);
```

5. 事件调度

```php
$user = User::find(1);

$event = $dispatcher->dispatch(new UserRegistered($user));
```

6. 可终止事件

> 事件实现`StoppableEventInterface`接口中的`isPropagationStopped`方法，并且返回true，则不会触发该事件之后的事件

```php
class UserRegistered implements StoppableEventInterface
{
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function isPropagationStopped() : bool 
    {
        return true;
    }
}
```

## max/aop

一款简单Aop实现。支持MaxPHP, Swoole，WebMan等框架

### 安装

```shell
composer require max/aop:dev-master
```

### 使用

> 以下以webman为例

#### 修改start.php文件

```php
\Max\Di\Scanner::init(new \Max\Aop\ScannerConfig([
    'cache'      => false,
    'paths'      => [
        BASE_PATH . '/app',
    ],
    'collectors' => [],
    'runtimeDir' => BASE_PATH . '/runtime',
]));
```

* cache 是否缓存，true时下次启动不会重新生成代理类
* paths 注解扫描路径
* collectors 用户自定义注解收集器
* runtimeDir 运行时，生成的代理类和代理类地图会被缓存到这里

#### 编写切面类，实现AspectInterface接口

```php
<?php

namespace App\aspects;

use Closure;
use Max\Aop\JoinPoint;
use Max\Aop\Contracts\AspectInterface;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Round implements AspectInterface
{
    public function process(JoinPoint $joinPoint, Closure $next): mixed
    {
        echo 'before';
        $response = $next($joinPoint);
        echo 'after';
        return $response;
    }
}

```

修改方法添加切面注解

```php
<?php

namespace app\controller;

use App\aspects\Round;
use Max\Di\Annotations\Inject;
use support\Request;

class Index
{
    #[Inject]
    protected Request $request;

    #[Round]
    public function index()
    {
        echo '--controller--';
        return response('hello webman');
    }
}
```

>
注意上面添加了两个注解，属性和方法注解的作用分别为注入属性和切入方法，可以直接在控制器中打印属性$request发现已经被注入了，切面注解可以有多个，会按照顺序执行。具体实现可以参考这两个类，注意这里的Inject注解并不是从webman容器中获取实例，所以使用的话需要重新定义Inject以保证单例

#### 启动

```shell
php start.php start
```

打开浏览器打开对应页面，控制台输出内容为`before--controller--after`

### 自定义收集器及注解

#### 定义收集器

```php
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

#### 注册收集器

收集器可以传递给ScannerConfig的collectors参数

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
    
    public static function getByClass(string $class) {
    	return self::$container[$class] ?? [];
    }
    
    public static function isValid(object $attribute): bool {
    	return $attribute instanceof ValidationAttribute;
    }
}

class DoSomething {
	
	protected $a = '1212';
	
	public function do() {
		$properties = ValidationCollector::getByClass(__CLASS__);
    	$len = $properties['a']->max;
        if(mb_strlen($this->a > $len)) {
        	throw new InvalidArgumentException('Length is invalid.');
        }
	}
}
```

### 切面

#### 创建切面类

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
    protected CacheInterface $cache;

    public function __construct(protected int $ttl = 0, protected ?string $key = null)
    {
        $this->cache = make(CacheInterface::class);
    }

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

#### 添加注解

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

## max/session

可以独立使用的Session组件，支持File和Redis存储

### 安装

```php
composer require max/session:dev-master
```

### 使用

```php
// 初始化SessionManager
$sessionManager = \Max\Di\Context::getContainer()->make(\Max\Session\SessionManager::class);

// 创建一个新的Session会话
$session = $sessionManager->create();

// 开启会话
$session->start($id); // 如果为null则创建id

// 设置
$session->set($key, $value);

// 获取，支持点语法
$session->get($key);

// 请求结束，保存session
$session->save();

// 关闭会话
$session->close();
```

> 中间件的代码参考

```php
<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use Max\Http\Message\Cookie;
use Max\Session\SessionManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionMiddleware implements MiddlewareInterface
{
    protected int    $expires  = 9 * 3600;
    protected string $name     = 'MAXPHP_SESSION_ID';
    protected bool   $httponly = true;
    protected string $path     = '/';
    protected string $domain   = '';
    protected bool   $secure   = true;

    public function __construct(protected SessionManager $sessionManager)
    {
    }

    /**
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = $this->sessionManager->create();
        $session->start($request->getCookieParams()[strtoupper($this->name)] ?? null);
        $request  = $request->withAttribute('Max\Session\Session', $session);
        $response = $handler->handle($request);
        $session->save();
        $session->close();
        $cookie = new Cookie(
            $this->name, $session->getId(), time() + $this->expires, $this->path, $this->domain, $this->secure, $this->httponly
        );

        return $response->withAddedHeader('Set-Cookie', $cookie->__toString());
    }
}

```

## max/http-server

多环境兼容的 Http Server

### 设计思想

> 全部符合psr规范

request -> kernel -> response

### 使用

```php

// 实例化router
$router = new \Max\Routing\Router();

// 注册路由
$router->get('/', 'IndexController@index');

// 实例化kernel，注意这个需要保持单例
$kernel = new \Max\Http\Server\Kernel($router->getRouteCollector(), \Max\Di\Context::getContainer());

// 获取一个PsrServerRequest
$request = \Max\Http\Message\ServerRequest::createFromGlobals();

// 返回PsrResponse
$response = $kernel->through($request);

// 发送响应
(new \Max\Http\Server\ResponseEmitter\FPMResponseEmitter())->emit($response);

```

> 框架内置三种环境的ResponseEmitter，均可以自定义

### 示例

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

## max/view

`MaxPHP` 视图组件，支持`Blade`，可扩展驱动，可独立使用

### 安装

```
composer require max/view
```

### 使用

> Blade引擎支持的语法如下

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

#### 配置文件

安装完成后框架会自动将配置文件`view.php`移动到根包的`config`目录下，如果创建失败，可以手动创建。文件内容如下：

```php
<?php

return [
    'engine'  => '\Max\View\Engines\Blade',
    'options' => [
        // 模板目录
        'path'        => __DIR__ . '/../views/',
        // 编译和缓存目录
        'compile_dir' => __DIR__ . '/../runtime/cache/views/compile',
        // 模板缓存
        'cache'       => false,
        // 模板后缀
        'suffix'      => '.blade.php',
    ],
];

## 使用

```php
$viewFactory = new ViewFactory(config('view'));
$renderer = $viewFactory->getRenderer();
$renderer->assign('key', 'value');
$renderer->render('index', ['key2' => 'value2']);
```

#### 自定义引擎

自定义引擎必须实现`ViewEngineInterface`接口

## max/cache

存组件基于`Psr-16`开发，支持`file`，`memcached`，`redis`，`Apc`， 该组件可以独立使用

### 安装

```shell
composer require max/cache:dev-master
```

### 使用

```php
public function index(\Psr\SimpleCache\CacheInterface $cache) {
    var_dump($cache->get('key'));
}
```

#### 扩展功能

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

## max/context

文档待补全

## max/database

> 文档更新不及时，可能会有大量出入

### 安装

> 文档更新不及时，使用方法有很大出入

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

#### 新增

```
$query->table('users')->insert(['name' => 'username','age' => 28]);
```

#### 删除

```
$query->table('users')->where('id', '10', '>')->delete();
```

删除id大于10的用户。

#### 更新

```
$query->table('users')->where('id', '10', '>')->update(['name' => 'zhangsan']);
```

#### 查询

> 查询构造器

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

#### 模型

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

## max/http-message

文档待补全

## max/queue

文档待补全

## max/redis

文档待补全

## max/utils

文档待补全

## max/validator

文档待补全
