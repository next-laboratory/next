# 安装

>推荐用开发版！

## 要求

```
PHP >= 7.2.0
```

> 如果你没有使用过composer 可以先了解一下这块的知识 -> [学习笔记](https://www.chengyao.xyz/note/128.html)

## 使用Composer安装：

```
composer create-project --prefer-dist max/max .
```

如果你想要使用开发版，可以使用下面的命令安装

```
composer create-project --prefer-dist max/max:dev-master .
```

这行命令会在你命令执行目录安装框架

> 你可以使用Git拉取框架

```
git clone https://github.com/topyao/max.git .
```

在项目目录使用composer安装依赖

```
composer install
```

安装完成后就可以使用 `php max serve` 启动服务。框架强制路由，所以在编写控制器前应该先定义路由规则，如果你的环境是`windows`需要修改`public/.htaccess`中的`RewriteRule`或者`nginx`
伪静态规则，在`index.php`后面加上`?`。框架对数据类型比较敏感，例如在该设置为`true`时候不要设置`1`。否则会报错。

## 伪静态

下面提供了`apache`和`nginx`的伪静态配置

> apache

```
<IfModule mod_rewrite.c>
	Options +FollowSymlinks -Multiviews
	RewriteEngine On
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteRule ^(.*)$ index.php/$1 [QSA,PT,L]
</IfModule>
```

> Nginx

```
if (!-d $request_filename){
	set $rule_0 1$rule_0;
}
if (!-f $request_filename){
	set $rule_0 2$rule_0;
}
if ($rule_0 = "21")
{
	rewrite ^/(.*)$ /index.php/$1 last;
}
```

> 注意如果你的环境是windows,可能需要给伪静态规则中的`index.php/`后面加上`?`

# 容器

> 这里的容器主要作用有两个，存放一些类的实例，实现构造方法、__setter方法、普通方法和闭包的依赖注入

## 绑定标识

```
\Max\Facade\App::alias($id, $className);
```

以后使用容器的API就不需要传入完整类名

## 绑定标识和参数

```
\Max\Facade\App::bind($id, $bind, $arguments = [], $renew = false)
```

首先绑定标识$id 给$bind, 并且参数是会保存在容器中，此后实例化该类的时候会将$arguments 传递给构造函数

## 实例化类

> 使用容器实例化类

```
$obj = \Max\Facade\App::make($className,$arguments = [],$renew = false);
```

第一个参数传入一个完整类名，第二个参数是传递给类构造方法的参数列表数组，第三个参数为true时候表示获取一个单例，在后面请求中获取类实例的`$renew
为`true`的时候会创建新对象，而不是从容器中获取已经实例化并且依赖注入的对象。

此时`$obj`是一个给构造方法实现依赖注入的实例，在后面的调用实例的方法时候并不会给方法实现依赖注入

> 可以使用框架提供的`app()`助手函数，函数可选多个参数，当不传递参数的时候会返回`app`实例，否则第一个参数接受一个字符串，容器会实例化该类

## 方法调用

> 使用容器调用实例的方法

```
\Max\Facade\App::invokeMethod([$className,$method],$arguments = [],$renew = false,$constructorArguments = []);
```

第一个参数为一个数组，数组的第一个元素为需要实例化的类名，第二个元素为要调用的方法名。第二个参数为给方法传递的参数列表，第三个方法表示实例化的类是不是单例的，第四个参数为实例化类过程中给构造方法传递的参数列表

> 可以使用框架提供的助手函数`invoke()`

## 闭包的依赖注入

```
\Max\Facade\App::invokeFunc(function(\Max\Http\Request $request)
{    
	//...
},array $arguments);
```

> 可以使用框架提供的助手函数`invoke()`

## 获取实例

> 获取容器内的实例可以使用`get`方法

```
\Max\Facade\App::get($className);
```

> 获取实例可以使用还可以使用`make`方法,`app`实例的对象属性访问方式，`app`实例的属性数组访问方式。

## 判断存在与否

> 判断容器中的实例是否存在可以使用

```
\Max\Facade\App::has($abstract);
```

> 注意：控制器方法是始终实现依赖注入的。

# 目录结构

## 结构

- app 应用目录
    - Console 命令行
    - Http
        - Controllers 控制器目录
        - Middleware 中间件目录
        - Requests 请求目录
        - Controller.php 用户自定义基础控制器类
        - Kernel.php http启动引导
        - Error.php 自定义错误类
        - Request.php 自定以请求基类
    - Models 模型目录
    - Facade 用户自定义门面目录
    - common.php 用户自定义函数文件
- config 配置文件目录
    - app.php 应用配置文件
    - session.php Http服务配置文件
    - cache.php 缓存配置文件
    - database.php 数据库配置文件
    - view.php 视图配置文件
- public 应用访问目录
    - .htaccess apache伪静态文件
    - nginx.conf nginx伪静态文件
    - index.php 入口文件
- routes 路由目录
    - api.php api路由
    - web.php web路由文件
- vendor 扩展包（包含框架核心）
- views 视图目录
- .env 环境变量文件
- .example.env 环境变量示例文件
- .htaccess 伪静态文件
- composer.json composer配置文件
- composer.lock composer锁定文件
- LICENSE 开源许可证
- README.md 手册
- max 命令行文件
- server.php 开发环境运行程序使用的文件

> 框架对单/多应用这个概念比较模糊，只是在定义路由和渲染模板的时候应该有所注意，这里在下面的章节中会提到。

## 自定义目录[开发版]

> 通常我们都使用public目录作为入口目录，但是有时候我们会用到虚拟主机，部分虚拟主机又不能绑定子目录，所以添加了下面的功能


这里介绍将public目录转移至项目根目录的操作，按照下面的步骤操作即可：
① 将public目录的文件全部转移至根目录

② 修改index.php 入口文件

```php
<?php

namespace Max;

//更改为require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/../vendor/autoload.php';

//更改为(new App())->setRootPath('./')->start(function (App $app) {
//或者更改为(new App('.'))->start(function (App $app) {
(new App())->start(function (App $app) {
    $http     = $app->http;
    $response = $http->response();

    $http->end($response);
});
```

# 配置

配置文件包含两种，一种是`config`目录下的以小写字母开头的`.php`为后缀的文件，另一种是框架根目录下的`.env`文件，下面简单介绍下如何使用他们。

## ENV

在开发环境下大多数配置都可以通过`.env`文件获取，而且默认为从`.env`文件获取，线上环境需要删除`.env`文件或者将配置中的`env`去掉，例如在`app.php`
中有这样`'debug' => env('app.debug', false),`一个配置，我们可以更改为`'debug' => false,` .env文件可以使用节，例如：

```
[APP]
DEBUG=true #开启调试AUTO_START=true
```

其中app就是一节，获取`DEBUG`配置可以使用`env('app.debug')`或者`\Max\Facade\Env::get('app.debug');`

## config

配置文件是位于`config`目录下，以`.php`结尾的返回一个关联数组的文件。

获取所有配置使用

```
\Max\Facade\Config::get();
```

可以传入一个参数例如`app`,则会获取`app.php`文件中的配置，传入`app.auto_start` 则获取`app`中的`auto_start`参数的值

如果需要自定义一个配置文件，可以在`/config`目录下新建例如`alipay.php`文件并返回一个数组。

```
\Max\Facade\Config::get('alipay.param');       //获取配置
```

> 可以使用辅助函数`config()` ，例如`config('app.debug')`

# Facade(门面)

Facade基本代码示例如下：

```php
<?php

namespace App\Index\Facade;

use Max\Facade;

class UserCheck extends Facade{    
	
	protected static function getFacadeClass()    
	{        
		return \App\Index\validate\UserCheck::class;    
	}
}
```

> 你可以在任何可以composer自动加载的位置创建类并继承Max\Facade类，就可以实现静态代理，但是为了方便维护，建议创建在应用对应的facade目录下。
>
> 注意: Facade默认实例化的对象都是会保存在容器中的。如果不需要保存，可以加入`protected static $renew = true;`，当然仅仅是在你的请求的页面中使用该类的方法全部为Facade或者依赖注入或者容器获取的实例的时候才是最早实例化的类。
>
> Facade还有一个属性，protected static $methodInjection = false;当为true的时候该门面调用的类支持对普通方法的依赖注入

# 服务

服务实现基本代码如下

```php
<?php
    
namespace App\Index\Services;

use \Max\App; 

class Test implements Service{
    
    public function register(){        
        App::instance()->bind('test', \Test::class);
    }    
    
    public function boot()    
    {        
        echo '1';    
    }
}
```

定义如上服务类，并将类注册到`/config/app.php`中的`provider`中，这样在`app`启动的时候会首先调用`register`方法注册服务，注册完成后立即调用`boot`方法。上面的代码注册了`Test`
的服务,当`app`启动会先向容器中绑定`test`类。

# 路由

路由存放的文件有两个`web.php` , `api.php` 当当前请求方式是`ajax`的时候只会注册并匹配`api.php`中的规则，否则只会注册并匹配`web.php`中的路由

> 注意：需要引入`\Max\Facade\Route`类

## 路由定义

路由的定义方式有三种：字符串，数组，闭包

路由定义需要设置请求方式例如

```
Route::get('路由地址','路由表达式');   //get方式请求的路由  Route::post('路由地址','路由表达式');  //post方式请求的路由
```

请求方式可以是`get,post,put,delete,patch`等请求类型,以下非特殊都是用`get`做演示，这里的路由地址是不包含`queryString`的，即使`url`中有`queryString`,也会匹配该路由。

## 字符串

当我们使用单应用模式的时候，按照下面的方法定义路由

```
Route::get('路由地址','控制器@方法');
```

例如

```
Route::get('index','index/index@index');
```

这里的`index/index@index`中`@`后的字符串为调用的方法名，前面的组成类似`App\Http\Controllers\Index`的类名,对应到目录中为`/app/Http/Controllers/index.php`
这样就很容易理解如何创建单、多应用。只需给路由表达式添加多个斜线分割，对应于类所处的文件夹上下级。

## 数组

使用数组的方式定义路由，数组的第一个参数必须是一个控制器类的完整类名，第二个参数为方法名字符串，例如

```
Route::get('index',['\App\Http\Controllers\Index','index']);
```

表示映射到`\App\Http\Controllers\Index`控制器的`index`方法

## 闭包

```
Route::get('index',function(){       return view('模板文件');});
```

> 注意：这里使用到了view助手函数，当路由地址中含有参数时可以给闭包函数传递相应参数，在下面会提到。

## 路由高级

## 多请求类型路由

当我们一个url需要多种请求方式来访问的时候可以定义`rule`类型的路由，例如：（这里的/非必须，但是建议加上）

```
Route::rule('/', 'index/index@index', ['get', 'post']);
```

第三个参数传入请求方式数组，可以为空，为空默认为`get`和`post`

## 正则表达式与参数传递

在上面提到了路由是可以给控制器方法或者闭包传递参数的。

例如我定义了一个如下的路由

```
Route::get('/article/index(\d+)\.html', 'index/article@read');
```

该路由的第一个参数是一个不带定界符的正则表达式，该表达式会匹配`/article/index任意数字.html`的请求地址，这个正则中使用了一个匹配组`(\d+)`,并且这个组是第一次出现的，那么就可以在控制器方法或者闭包中传入一个参数。

> 给闭包传参

```
Route::get('/article/index(\d*)\.html',function($id = 0){    echo $id;});
```

> 注意：这里的闭包是可以实现依赖注入的
>
> 给控制器方法传参

```
public function read($id = 0){    echo $id;}
```

可以传入多个参数,匹配组出现的顺序就是传递给方法或者闭包的参数顺序,例如：

```
Route::get('/(\w+)-index(\d+)\.html',function($a,$b){    echo $a,$b;});
```

访问`blog-index2.html` 时会输出`blog` 和 `2`

> 注意：正则路由中的正则不需要添加定界符，多个参数是按匹配到的顺序传递的。转义符号务必使用反斜线，否则url助手函数可能不能正确获取到正则路由的地址

## 路由支持注册别名，例如

```
Route::get('/','index/index@index')->alias('index');
```

之后就可以在任意位置使用`url`助手函数获取路由地址，例如`url('index')` 返回`/`，如果`url()` 函数中传入的参数并没有被注册别名，那么会原样返回。`url`函数可以添加第二个参数来给正则路由传递参数，例如

```
Route::get('/b(.*)\.html','index/index@index')->alias('blog');
```

此时可以使用`url('blog',[1]);` 生成的`url`地址为`/b1.html` ，这里`url`的第二个参数为一个索引数组，参数按照在数组中的顺序传递。

## 路由可以设置缓存

> 缓存目前因为一个重大问题暂时不要使用，当路由中不存在闭包时候没有影响

```
php max route   //根据提示选择选项
```

设置缓存文件后路由不会再通过调用`/route`下文件中的大量方法来注册，而是直接从缓存文件中读取，所以在开发环境上建议不要使用路由缓存，否则新增或删除路由不能及时更新

## 其他规则路由

> None路由

使用

```
Route::none(function(){    
	//return view('index/index');
},$data = []);
```

创建一个`none`路由，当所有路由未匹配到时会匹配该路由，需要给第一个参数传入一个闭包，第二个参数可选地传入一个索引数组，数组的每一个值都会按照数组的索引顺序传入闭包中，闭包中需要有相应形参或其他方式来获取传值。

> 视图路由

```
Route::view('index','index/index',['get']);
```

该路由表示`get`方式请求的`/index`会被映射到`views`目录下的`index`目录下的`index.html`模板文件,分隔符后最后的部分为模板文件名，前面均为目录名。最后一个参数为可选参数，为空默认为`get`
方式请求的路由；

> 重定向路由

```
Route::redirect('index','https://www.1kmb.com',302,['get']);
```

该路由表示`get`方式请求的`/index`会被重定向到`https://www.1kmb.com`。后两个参数为可选参数，第一个为跳转状态码，默认为`302`；第二个为请求方式，默认为[‘get’]；

## 跨域支持

> 框架支持跨域功能，在定义路由的时候可以设置跨域，可以设置全局跨域，只需要将`app.php`配置文件中的`GlobalCross`中间件的注释取消即可

```
Route::get('/','index/index/index')->cors('*');
```

> 注意：这里`cors()` 方法的参数可选，参数一：`$AllowOrigin` 允许跨域域名，可以设置一个’*’或者字符串完整`url`或者数组的完整`url`，参数二：`$AllowCredentials` 是否可以将对请求的响应暴露给页面，参数三：`$AllowHeaders `允许的头信息，参数四`$Maxage`预检缓存有效期

# 请求

## 获取请求参数

获取请求可以用`Facade`

```
\Max\Facade\Request::get()
```

如果需要获取某一个参数，可以使用

```
\Max\Facade\Request::get('a');
```

可以给第二个参数传入一个默认值，例如

```
\Max\Facade\Request::get('a','default');
```

获取多个参数可以使用

```
\Max\Facade\Request::get(['a','b']);
```

可以传入一个关联数组，数组的键为参数名，值为默认值，例如

```
\Max\Facade\Request::get(['a' => 1,'b' => 2]);
```

此时如果`a`不存在，则`a`的值为`1`

获取`post`请求的内容

```
\Max\Facade\Request::post();
```

获取原始数据

```
\Max\Facade\Request::raw();
```

获取所有`$_REQUEST`使用

```
\Max\Facade\Request::input();
```

> `post`和`param`使用方法和get一样。可以给这些方法添加第二个参数，第一个参数为字符串且不存在的时候会返回默认参数

## 获取$_SETVER 变量

替代地，可以使用`Request::server($name)` 获取`$_SERVER`中的值，如果不存在返回`null`

## 获取header

可以使用`Request::header();` 获取所有`header`信息，可以传入一个参数，例如`Request::header('user_agent')`;获取`UA`

## 判断请求类型

使用`isMethod`方法判断任何请求类型，例如判断请求类型是否为`get`

```
\Max\Facade\Request::isMethod('get');
```

还可以判断是否是`ajax`请求

```
\Max\Facade\Request::isAjax()
```

## 可以使用基础控制器

当控制器继承了基础控制器`App\Http\Controller`或者`\Max\Http\Controller`后就可以直接使用

```
$this->request->get();
```

的方式获取参数，其他用法和`Facade`类似

## 请求缓存

请求缓存可以在路由中配置，只需要在注册路由中使用连贯操作调用`cache()`方法，传入一个整数，表示请求缓存的时间/秒,例如：

```
Route::get('index','index/index')->cache(600);
```

表示该请求可能会缓存`600`秒

## 参数过滤

参数过滤使用中间件`App\Http\Middleware\VariablesFilter`完成，该中间件中有两个属性， $filters 过滤函数和$vars 需要过滤的全局数组

```
protected $filters = [
    'trim',
    'htmlspecialchars|3',
];
```

其中，要过滤的数据总数在函数列表中的第一个参数中，例如htmlspecialchars($value, 3); 如果有多个参数可以使用:分割，函数名和参数使用|分割

```
protected $vars = [
    'GET',
    'POST',
    'REQUEST',
];
```

# 中间件

> 支持app全局, 路由注册后全局，单个路由，控制器前后置中间件 中间件优先级 app全局  > 路由注册后 > 路由 > 控制器

首先需要创建一个中间件，例如

```php
<?php
    
namespace App\Http\Middleware;

use Max\Facade\Session;

class Login{    
    
    public function handle($request, \Closure $next)    
    {        
        if(!Session::get('user'))
        {            
            exit(view('index/404'));
        }
        $response = $next($request);
        echo '执行完了’;        
        return $response;    
}
```

## 全局中间件

在\App\Http\Kernel::class 中的$middleware数组中配置的为全局中间件，包含app和route，app中的中间件在app启动时运行，route中的在路由匹配之后运行

## 控制器中间件

可以使用两种方式添加控制器中间件

### 属性

如果你没有继承基础控制器类，则可以添加$middleware属性

```
protected $middleware = [
	UACheckMiddleware::class => [
		'only'   => [],
		'except' => [],
	],
];
```

如果你继承了基础控制器，则你的控制器可以这么写

```
public function __construct() {
	$this->middleware(UACheckMiddleware::class, $only = [], $except = []);
}
```

## 路由中间件

可以使用middleware方法注册一个路由中间件，例如

```
Route::get('index','index/index')->middleware('\\App\\Http\\Middleware\\Login::class);
```

> 不管路由中注册的是闭包还是类名，都会经过中间件,目前的中间件实现很low所以不要在中间件中return $next($request);

# 响应

在控制器中可以直接`return`一个数组，框架会自动转为`json`输出，例如`resopnse(array $data,202,['Content-Type:application/json']);`
第一个参数为数据，第二个为状态码，第三个为头信息。

# 验证器

要使用验证器，需要安装验证器组件

```
   composer require max/validator:dev-master
```

> 以下文档没有更新，以实际使用为准。使用`throwAble`方法可以设置是否抛出异常
> 需要验证的数据必须是数组，比如通过Request类方法传入数组获取的数组。

## 控制器验证

```
public function test(){   $data = Request::post();   $vali = Max\Facade\Validate::rule([       'username' => ['required' => true, 'max' => 10, 'regexp' => '\w+']   ])->check($data);   if (true !== $vali) {       exit($vali);   }}
```

验证器操作必须按照如上代码写，可以在`App\Validate`类中添加方法例如`_checkUservali`
，验证成功返回true，失败将消息新增到成员属性$message数组后返回false。之后使用rule方法的时候就可以使用rule([‘username’ => [‘uservali’ => 1])
;验证username字段。当一个字段验证失败后就不会再验证其他字段了！ 验证器支持全部验证，只需要给check方法的第二个参数传入true即可开启全部验证，非批量验证失败返回消息字符串，批量验证失败返回消息索引数组。

> 默认可用的验证器有max,min,length,enum,required,regexp,bool,confirm

`_checkBool` 当值为`'on', 'yes', 'true', true, 1, '1'`时为真，相反为假，在验证规则中应该传入true或者false 在验证`regexp`
的时候需要编写完整验证正则表达式包括定界符，例如：`/\d+@\w+\.\w{3}/i` 注意这里验证使用了`preg_match`，所以在编写正则表达式的时候应该注意，例如不要使用模式修正符g

## 独立验证

添加了独立验证，你可以在任何可以`composer`自动加载的位置添加验证器类例如`UserVali`，并继承`App\Validate`，在该类中添加验证规则$rule

```
protected array $rule = [    'username' => ['required' => true]];
```

如果你继承了基础控制器，就可以使用下面的方法进行数据验证

```
$res = $this->validate(UserVali::class,$data);
```

第一个参数为需要验证的数据，第二个参数为验证器类名完整字符串，注意这里追加的规则时使用`+`语法将两个数组合并，可能导致覆盖，独立验证可以设置属性$checkAll用来设置是否是全部验证,当`$checkAll`
为true时开启批量验证，否则一旦有验证失败的条目都会结束验证。独立验证可以使用连贯操作和追加删除操作，例如

```
$vali = $this->validate(UserVali::class,$data)->min(['a' => 1])->remove(['password' => ['required']])->append(['password' => ['min' => 1]])->check();
```

验证器随意增加或者删除验证规则,例如：

```
Validate::rule(['a' => ['required' => true])->append(['a' => ['max' => 2,'min' => 1]])->append(['b' => ['required' => true])->remove('a')->remove(['a' => ['required','max'])->remove(['a' => 'min'])->check($data);
```

## 验证器还可以使用连贯操作

```
$vali = Validate::data($data)->max(['a' => 10])->required(['a' => true,'b' => true)->check();
```

或者使用

```
(new Validate($data))->max('a',10)->check();
```

或者使用

```
(new Validate())->max('a',10)->check($data);
```

注意：这的`Validate`可以是任意你新建的继承了`App\Validate`或者`\Max\Validate`
的类，只是前者会包含你自定义的验证规则，后则会不包含，并且如果在你的验证器类中存在rule属性的设置就不再需要传入rule规则了，直接使用check即可。如果验证成功会返回true，否则返回带有错误信息的数组，使用闭包验证时func方法第一个参数传入要验证的字段，第二个参数传入闭包，第三个参数可选传入传递给闭包的参数列表数组。

## 验证提示

可以自定义验证规则的提示，只需要给属性notice设置和验证规则类似的数组，并且将验证的限定值改为提示信息即可。

> 验证器支持设置验证失败抛出异常，只需将独立验证的属性throwAble设置为true，或者给check方法传入第三个参数true，即可开启抛出异常，批量验证失败抛出异常信息为json。

框架验证器内置诸多规则

required 参数必须 max min length regexp bool in confirm

# 控制器

假如我定义了以下路由

```
Route::get('/','index/index/index');
```

如果需要编写控制器代码，就需要编写`/app/Http/Controllers/Index`目录下的`Index.php`控制器里的`index`方法

控制器的基本代码如下：

```
<?phpnamespace App\Http\Controllers\Index;class Index{    public function index()    {    }}
```

> 控制器可以继承\App\Http\Controller 基础控制器来使用基础控制器中提供的方法，你也可以自定义基础控制器
>
> 可以给控制器方法传入参数，参数个数和位置取决于路由中正则匹配到的参数。 当路由中的参数为可选，就应该给控制器参数一个初始值
>
> 如果你继承了基础控制器，那么会有两个属性可以提供使用，$this->request,$this->app ，分别时请求对象和app对象，app对象是来管理容器中实例的，使用方法如下：

```
$this->app['完整类名'];
```

这是会直接返回该类的实例，并且其构造方法是实现依赖注入的，如果该类是单例的，并且使用

```
$this->app->has('完整类名');
```

返回true，那么,例如该类绑定的标识为request，可以直接使用$this->app->request获取容器中的实例。

> 当继承了基础控制器后不再建议使用构造函数初始化，而是使用init() 方法进行初始化。

```
public function init(){}
```

# 模型[不可用]

模型的目录在app\Models下，新建模型继承Max\Database\Model类即可使用模型提供的方法。模型名为表名，也可以在模型中设置name属性，此时的表名就是name的值。
例如我新建了一个Notes的模型，可以在模型中直接使用 $this->where([‘name’ => ‘cheng’])->select() 进行查询

## 初始化

当类继承了Max\Database\Model类后就不要再使用构造函数初始化了，而是采用init方法进行初始化

# 视图

## 使用内置模板驱动

模板引擎可以使用twig或者smarty，可以在config/view.php中设置模板引擎。

> 注意：需要手动改安装对应模板引擎；

视图目录位于根项目目录下views文件夹，可以使用助手函数渲染模板

```
view('index/index');
```

这里的第一个参数和控制器解析规则类似，表示/views/index/index.html 模板文件，这里的模板后缀可以在`/config/view`中修改`suffix` 选项

模板渲染方法可以传入第二个数组参数用来给模板赋值，例如

```
view('index',['data'=>$data]);
```

或者使用`Facade`

```
\Max\Facade\View::render('index',$params);
```

> 你可以使用composer安装你喜欢的模板引擎

## 自定义驱动

框架允许你自定义任何视图驱动，可以在任何可以composer自动加载的位置定义视图驱动，并且在视图配置view.php中将type和视图配置名改为你的视图驱动完整类名。

视图驱动需要继承\Max\View\Driver.php 并且实现public function render($params){} 和init方法。继承该类后可以使用$this->template
获取需要渲染的模板文件名。使用方法可以参考内置的视图驱动。

# 数据库

https://www.chengyao.xyz/note/212.html

### 历史SQL

> 可以使用门面的方式

```
\Max\Facade\Db::getHistory();
```

或者直接使用Query类

```php
public function index(Max\Database\Query $query){
	\Max\Facade\Db::name('users')->where(['id' => 1], '>')->select();
	halt($query->getHistory());
}
```

会返回一个二维数组，包含每条SQL和SQL执行时间

# 缓存

## 安装

使用缓存需要安装缓存组件

```
composer require max/cache:dev-master
```

缓存组件支持`file`,`memcached`,`redis`

## 使用

安装完成后可以使用缓存的门面，例如

```
\Max\Facade\Cache::get($key);
\Max\Facade\Cache::set($key,$value,$timeout = null);
```

也可以使用`cache()` 助手函数，该函数返回`Cache` 实例，可以使用如下方法

```
cache()->get($key);
cache()->set($key,$value,$timeout = null);
```

# 其他

## Redis

需要使用更丰富的`Redis`操作需要安装`Redis`组件

```
composer require max/redis:dev-master
```

该扩展不要求使用MaxPHP，你可以在任何地方使用，仅作为一个扩展。支持单、多台`redis`,支持读写分离

具体使用方法可以查看`redis` 扩展的`README.md`介绍

## 命令操作

使用`php max help`命令查看支持的命令

框架提供了不丰富的命令操作，目前支持路由列表，路由缓存，启动内置服务等操作；只需要在项目目录下打开命令窗口输入以下指令就可以方便地进行各种操作。

```shell
php max        //所有命令的帮助
php max serve  //会提示输入一个端口来创建服务，默认为8080
php max route  //会提示输出列表或者路由缓存操作，根据提示输入数字即可
```

用户可以自定义命令，命令类必须有一个`exec`方法，大部分代码都写在这里面。命令可以配置信息用于使用help命令时候的显示，例如

```php
public function configure()
{
    $this->setName('create')
        ->setDescription('Description');
}
```

自定义命令后需要在·`app/Console/Console.php` 中注册命令，注册格式如下：

```php
protected $register = [
    'create' => Create::class
];
```

当你执行`php max create`的时候相当于调用了`Create::class`的exec方法

## 助手函数

### dd()  / dump()

如果你安装的时候没有指定--no-dev 则可用(参考symfony/var-dumper)

### abort()

异常抛出

### view()

> 前提是安装了Max-View组件

视图渲染

### db()

> 前提是安装了Max-Database组件

数据库



### response()

响应

### url()

获取别名注册的url

## 认证

框架提供了一个简单的Basic认证中间件`\App\Http\Middleware\BasicAuth::class`,可以在`app.php`配置文件中添加该中间件，新建配置文件`auth.php`，文件内容如下

```php
<?php

return [
    'basic' => [
        'user' => 'pass'
    ]
];
```

> 注意：如果你使用了`apache`,那么需要在配置文件中加入`SetEnvIf Authorization .+ HTTP_AUTHORIZATION=$0`

## 错误处理

在MaxPHP中，错误和也被当作异常处理，所以只需要定义异常处理的类即可。

如下定义了一个异常处理类：

```php
<?php


class Handler extends \Max\Exception\Handler
{
    public function __toString()
    {
        return <<<ETO
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Something went error！</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }
        body {
            display: flex;
            flex-direction: column;
            height: 100vh;
            text-align: center;
        }
        .box {
            flex: 1;
            display: flex;
            justify-content: center;
            flex-direction: column;
        }
        .number {
            font-size: 80px;
            color: #666;
            font-weight: bold;
        }
        .text {
            font-size: 14px;
            margin: 24px;
            color: #333;
        }
        .btn-container {
            display: flex;
            justify-content: center;
        }
        .btn {
            padding: 8px 24px;
            text-decoration: none;
            background: #fff;
            border: 2px solid #efefef;
            color: #333;
            margin: 24px;
            border-radius: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
        }
        .footer {
            padding: 16px;
            border-top: 1px solid #efefef;
            color: #777;
            font-weight: lighter;
        }
        .footer a {
            text-decoration: none;
            font-weight: bold;
            color: #000;
        }
    </style>
</head>
<body>
<div class="box">
    <div class="number">Sorry!</div>
    <div class="text">
        Something went error！
    </div>
    <div class="btn-container">
        <a class="btn" id="back">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" style="margin-right: 8px;">
                <path fill="none" d="M0 0h24v24H0z"/>
                <path d="M5.828 7l2.536 2.536L6.95 10.95 2 6l4.95-4.95 1.414 1.414L5.828 5H13a8 8 0 1 1 0 16H4v-2h9a6 6 0 1 0 0-12H5.828z"/>
            </svg>
            Back
        </a>
    </div>
</div>
<footer class="footer">
    Powered by <a href="https://www.chengyao.xyz" target="_blank">MaxPHP</a>
</footer>
<script>
    var back = document.getElementById('back')
    back.onclick = function() { console.log('run...'); history.back() }
</script>
</body>
</html>
ETO;

    }
}
```

该类继承了框架的Handler类，并且重写了`__toString`方法。

如果继承了框架Handler类，那么就可以使用类成员属性$exception， 该属性即当前异常实例。

## 调试

框架提供了一个用于调试的中间件`\App\Http\Middleware\AppTrace,可以开启此中间件进行测试，改中间件不依赖于debug模式【当前可以查看加载的文件，执行过的SQL，开发中...】

# 支持

> 联系邮箱:`bigyao@139.com`，感谢：[PHPStorm](https://www.jetbrains.com/?from=topyao)