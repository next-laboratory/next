# 安装

提供两种安装方式：

## 1.composer安装：

> composer create-project chengyao/yao=dev-master .

注意这里因为是开发版本，所以安装完成后需要手动删除`vendor/chengyao`下的包中的`.git`文件夹

## 2.github安装

> git clone https://github.com/topyao/yao

会在该目录下创建一个`yao`的文件夹，该文件夹即项目目录，需要进入`yao`目录在cmd下更新依赖

>  composer install

安装完成后就可以使用 `php yao serve [-p 8080]` 运行程序（注意当路由中存在后缀例如`.html`时可能会出现`404`，这时应该考虑使用`apache/nginx`）。框架强制路由，所以在编写控制器前应该先定义路由规则，如果你的环境是`windows`需要修改`public/.htaccess`中的`RewriteRule`或者`nginx`伪静态规则，在`index.php`后面加上`?`。框架对数据类型比较敏感，例如在该设置为`true`时候不要设置`1`。否则会报错。

## 3.伪静态

下面提供了`apache`和`nginx`的伪静态配置

>apache

```
<IfModule mod_rewrite.c>
  Options +FollowSymlinks -Multiviews
  RewriteEngine On
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule ^(.*)$ index.php?/$1 [QSA,PT,L]
</IfModule>
```

>nginx 

```
if (!-d $request_filename){
	set $rule_0 1$rule_0;
}
if (!-f $request_filename){
	set $rule_0 2$rule_0;
}
if ($rule_0 = "21"){
	rewrite ^/(.*)$ /index.php/$1 last;
}
```

# 目录结构
- app  应用目录
  - http 基础服务目录
    - Controller.php
    - Event.php
    - Validate.php
  - index 应用目录
    - controller 控制器目录
    - event 事件目录
    - facade 用户自定义门面目录
    - migrate 迁移文件目录
    - model 模型目录
    - validate 验证器目录
    - view 视图目录
- config 配置文件目录
  - app.php 应用配置文件
  - database.php 数据库配置文件
  - view.php 视图配置文件
- extend 扩展类库目录【命名空间为\\】
- public 应用访问目录
  - .htaccess apache伪静态文件
  - index.php 入口文件
- route 路由目录
  - route.php	路由文件
- vendor 固件包
- .env 环境变量文件
- .env.example 环境变量示例文件
- .htaccess 伪静态文件
- composer.json composer配置文件
- composer.lock composer锁定文件
- LICENSE 开源许可证
- README.md 手册
- yao 命令行文件

> 框架支持单/多应用，单应用时需要将`app/index`下的目录全部放置在`app`下,在定义路由和渲染模板的时候应该有所注意，这里在下面的章节中会提到。

# 配置

配置文件包含两种，一种是`config`目录下的以小写字母开头的`.php`为后缀的文件，另一种是框架根目录下的`.env`文件，下面简单介绍下如何使用他们。

## ENV
在开发环境下大多数配置都可以通过.env文件获取，而且默认为从.env文件获取，线上环境需要删除.env文件或者将配置中的env去掉，例如在`app.php`中有这样`'debug' => env('app.debug', false),`一个配置，我们可以更改为`'debug' => false,`
.env文件可以使用节，例如：

```
[APP]
DEBUG=true #开启调试
AUTO_START=true 
```
其中app就是一节，获取`DEBUG`配置可以使用`env('app.debug')`或者`\yao\facade\Env::get('app.debug');`

## config
配置文件是位于`config`目录下，以`.php`结尾的返回一个关联数组的文件。

使用`yao\Config::get()`获取所有配置，可以传入一个参数例如`app`,则会获取`app.php`文件中的配置，传入`app.auto_start` 则获取`app`中的`auto_start`参数的值



如果需要自定义一个配置文件，可以在`/config`目录下新建例如`alipay.php`文件并返回一个数组。使用`Config::get('alipay.param')`获取配置的值

>可以使用辅助函数config() ，例如config('app.debug')


# 路由
所有的路由都添加在`route/route.php`文件中，如果需要分文件存放，可以将其他文件引入该文件

> 注意：需要引入`yao\facade\Route`类

## 路由定义

路由的定义方式有三种：字符串，数组，闭包

路由定义需要设置请求方式例如

> Route::get(路由地址,路由表达式)   get方式请求的路由    

> Route::post(路由地址,路由表达式) post方式请求的路由

请求方式可以是get,post,put,delete,patch等请求类型,以下非特殊都是用`get`做演示，这里的路由地址是不包含queryString的，即使url中有queryString,也会匹配该路由。

### 字符串

当我们使用单应用模式的时候，按照下面的方法定义路由

> Route::get('路由地址','控制器/方法');

如果是多应用

> Route::get('路由地址','应用@控制器/方法');

例如

> Route::get('index','index@index/index'); 

该路由会匹配`get`方式请求的`path`为`/index`的url，并路由到`index`应用下的`Index`控制器中的`index`方法

### 数组

使用数组的方式定义路由，数组的第一个参数必须是一个控制器类的完整类名，第二个参数为方法名字符串，例如

```php
Route::get('index',['\app\index\controller\Index','index']);
```

表示路由到`\app\index\controller\Index`控制器的`index`方法

### 闭包

```
Route::get('index',function(){
       return view('模板文件');
});
```

注意：这里使用到了view助手函数，当路由地址中含有参数时可以给闭包函数传递相应参数，在下面会提到。

## 路由高级

### 多请求类型路由

当我们一个url需要多种请求方式来访问的时候可以定义`rule`类型的路由，例如：（这里的/非必须，但是建议加上）

>Route::rule('/', 'index/index/index', ['get', 'post']);

第三个参数传入请求方式数组，可以为空，为空默认为`get`和`post`

### 正则表达式与参数传递

在上面提到了路由是可以给控制器方法或者闭包传递参数的。

例如我定义了一个如下的路由

```php
Route::get('/article/index(\d+).html', 'index/article/read');
```

该路由的第一个参数是一个不带定界符的正则表达式，该表达式会匹配`/article/任意数字.html`的请求地址，这个正则中使用了一个匹配组`(\d+)`,并且这个组是第一次出现的，那么就可以在控制器方法或者闭包中传入一个参数。

> 给闭包传参

```php
Route::get('/article/index(\d+)?.html',function($id = 0){
	echo $id;
})
```

> 给控制器方法传参

```php
public functin read($id = 0){
	echo $id;
}
```

可以传入多个参数,传入顺序按照preg_match之后的match数组去掉第一个之后的顺序传入,，例如：

```php
Route::get('/(\w+)-index(\d+).html',function($a,$b){
	echo $a,$b;
})	
```

访问`blog-index2.html` 时会输出`blog` 和 `2`

> 注意：正则路由中的正则不需要添加定界符，多个参数是按匹配到的顺序传递的。


# 请求
## 获取请求参数
获取请求可以用Facade `yao\facade\Request::get()` ，或者使用new Request(? array $filters = null) 可以不传参数，传入的参数必须是包含可以使用的过滤函数的数组，当独立使用时建议加上参数可以实现获取数据的过滤
使用`yao\facade\Request::get()`,获取所有`get`的参数列表，如果需要获取某一个参数，可以使用`yao\facade\Request::get('a');` 获取多个参数可以使用`yao\facade\Request::get(['a','b']);`
还可以获取`post`请求的内容

>yao\facade\Request::post();

获取所有`$_REQUEST`使用`yao\facade\Request::param();`
>post和param使用方法和get一样。可以给这些方法添加第二个参数，第一个参数为字符串且不存在的时候会返回默认参数

## 判断请求类型
判断请求类型是否为get
>yao\facade\Request::isMethod('get');

可以用来判断任何请求类型

判断是否ajax请求
>yao\facade\Request::isAjax() 

## 参数过滤
请求是可以设置函数进行过滤的，可以在`app.php`中的`filter`数组中添加过滤函数，注意函数必须只能传入一个参数，并且返回过滤后的字符串。如果使用`Request`类获取参数默认是被过滤的。不需要过滤可以使用`$_GET`数组。

注意：如果需要获取的参数不存在，该参数的值将会是null，例如`Request::get(['a','b'])`当b不存在的时候会是`null`，此时需要用`is_null`判断。

# 验证器

>需要验证的数据必须是数组，比如通过Request类方法传入数组获取的数组。

## 控制器验证
```
public function test()
   {
       $data = Request::post();
       $vali = yao\facade\Validate::rule([
           'username' => ['required' => true, 'max' => 10, 'regexp' => '\w+']
       ])->check($data);
       if (true !== $vali) {
           exit($vali);
       }
   }
```
验证器操作必须按照如上代码写，可以在app\http\Validate类中添加方法例如`_checkUservali`，验证成功返回true，失败将消息新增到成员属性$message数组后返回false。之后使用rule方法的时候就可以使用rule(['username' => ['uservali' => 1]);验证username字段。当一个字段验证失败后就不会再验证其他字段了！
验证器支持全部验证，只需要给check方法的第二个参数传入true即可开启全部验证，非批量验证失败返回消息字符串，批量验证失败返回消息索引数组。

> 默认可用的验证器有max,min,length,enum,required,regexp,bool,confirm

`_checkBool` 当值为`'on', 'yes', 'true', true, 1, '1'`时为真，相反为假，在验证规则中应该传入true或者false
在验证`regexp`的时候需要编写完整验证正则表达式包括定界符，例如：`/\d+@\w+\.\w{3}/i` 注意这里验证使用了`preg_match`，所以在编写正则表达式的时候应该注意，例如不要使用模式修正符g

## 独立验证
添加了独立验证，你可以在任何可以`composer`自动加载的位置添加验证器类例如`UserVali`，并继承`app\http\Validate`，在该类中添加验证规则$rule

```
 protected array $rule = [
        'username' => ['required' => true]
    ];
```
之后就可以在继承了基础控制器的控制器中使用控制器方法`$res = $this->validate($data,UserVali::class)->check();`进行数据验证，验证成功,第一个参数为需要验证的数据，第二个参数为验证器类名字符串，注意这里追加的规则时使用`+`语法将两个数组合并，可能导致覆盖，独立验证可以设置属性$checkAll用来设置是否是全部验证,当`$checkAll`为true时开启批量验证，否则一旦有验证失败的条目都会结束验证。独立验证可以使用连贯操作和追加删除操作，例如
> $vali = $this->validate($data, UserVali::class)->min(['a' => 1])->remove(['password' => ['required']])->append(['password' => ['min' => 1]])->check();

验证器随意增加或者删除验证规则,例如：
>  Validate::rule(['a' => ['required' => true])->append(['a' => ['max' => 2,'min' => 1]])->append(['b' => ['required' => true])->remove('a')->remove(['a' => ['required','max'])->remove(['a' => 'min'])->check($data);


## 验证器还可以使用连贯操作
```
$vali = Validate::data($data)->max(['a' => 10])->required(['a' => true,'b' => true)->check();
```
或者使用
> (new Validate($data))->max('a',10)->check();

或者使用
>(new Validate())->max('a',10)->check($data);

注意：这的Validate可以是任意你新建的继承了`app\http\Validate`或者`yao\Validate`的类，只是前者会包含你自定义的验证规则，后则会不包含，并且如果在你的验证器类中存在rule属性的设置就不再需要传入rule规则了，直接使用check即可。如果验证成功会返回true，否则返回带有错误信息的数组，使用闭包验证时func方法第一个参数传入要验证的字段，第二个参数传入闭包，第三个参数可选传入传递给闭包的参数列表数组。

## 验证提示
可以自定义验证规则的提示，只需要给属性notice设置和验证规则类似的数组，并且将验证的限定值改为提示信息即可。

> 验证器支持设置验证失败抛出异常，只需将独立验证的属性throwAble设置为true，或者给check方法传入第三个参数true，即可开启抛出异常，批量验证失败抛出异常信息为json。


# 控制器
假如我定义了一个`Route::get('/','index/index/index');`的路由
如果需要编写控制器代码，就需要编写`/app/index/controller`目录下的`Index.php`控制器下的`index`方法

控制器的基本代码如下：
```
<?php

namespace app\index\controller;

class Index
{
    public function index()
    {
    }
}
```
> 控制器可以继承\yao\Controller 基础控制器来使用基础控制器中提供的方法，你也可以自定义基础控制器

>可以给控制器方法传入参数，参数个数和位置取决于路由中正则匹配到的参数。
当路由中的参数为可选，就应该给控制器参数一个初始值

# 模板引擎

模板引擎使用了twig

模板目录位于`/app/应用/view` 目录，比如我在`/app/index/view/index`目录下有一个`index.html`的模板，我可以在控制器方法中使用`return view('index/index')`渲染模板，该方法可以传入第二个数组参数用来给模板赋值，例如

>view('index',['data'=>$data]);

或者使用facade的`yao\facade\View::fetch('template',$params);`

其中的`index`对应`app/index/view`目录下的`index.html`文件。
模板后缀可以在`/config/view`中修改`template_suffix`
配置文件中还可以修改默认的模板变量左右分隔符和缓存。默认缓存为关闭

> 你可以使用composer安装你喜欢的模板引擎

# facade

facade基本代码示例如下：
```
<?php

namespace app\index\facade;

use yao\Facade;

class UserCheck extends Facade
{
    protected static function getFacadeClass()
    {
        return \app\index\validate\UserCheck::class;
    }

}
```

> 你可以在任何可以composer自动加载的位置创建独立验证器类并继承yao\Facade类，就可以实现静态代理，但是为了方便维护，建议创建在应用对应的facade目录下。

>注意: facade默认实例化对象都不是单例的。如果需要使用单例，可以加入`protected static $singleInstance = true;`，当然仅仅是在你的请求的页面中使用该类的方法全部为facade的时候才是单例的。

# 数据库

很遗憾，由于本人技术有限，目前的`Db`类只对`mysql`支持良好，其他数据库暂时没有测试。如果有需求可以使用`composer`安装第三方的数据库操作类，例如：`medoo`，`thinkorm`

## 新增
> db('users')->insert(['name' => 'username','age' => 28]);
## 删除
> db('users')->where(['id' => 1])->delete();
## 查询
> yao\Db::name('表名')->field('字段')->where([条件])->limit(1,3)->find()/select();
查询到的是数据集对象，可以使用toArray或者toJson获取

## 更新
> db('users')->where('id > 10')->update(['name' => 'zhangsan']);

其中`field`默认为`*`，`where`可以传入字符串或者一维数组，`find`查询一条，`select`查询所有。`limit`可以传入1到2个参数，对应`mysql`的`limit`。目前只对`where`条件做了预处理

例如我可以写如下语句
> yao\Db::name('users')->field('username')->where(['age' => 19])->limit(1,2)->select();

表示我要查询users表中年龄为19的用户名，并且取出偏移量为2，限制条数为1的用户名。
当然可以使用`yao\Db::name('users')->select()` 查询全部
可以使用助手函数例如：`db($tableName)->select();`

添加`whereLike(array $array)` 需要传入一个数组，数组的键为字段名，数组的值为模糊匹配值，例如%name%
`whereLike(['a' => '%time%','b' => '%b%'])`

添加`whereIn(array $array)` 需要传入一个数组，数组的键为字段名，数组的值范围数组，例如
`whereIn(['a' => ['1','4'],'b' => ['c','d'])`

当然我们更方便地使用`exec`和`query`两个方法来操作数据库，一般`query`是对应于查询，`exec`用来增加删改。使用方法如下:
`Db::exec($sql,$data)`，其中sql可以是预处理语句，需要绑定的数据传入`data`，可以使用?占位符和:占位符,返回值为前一条语句影响的条数
`Db::query($sql,$data，$all)` 前两个参数和`exec`是一致的，第三个参数为true时查询出全部数据，为false时查询出单条数据(默认)。
例如
> Db::query('SELECT * from users where id > :id',['id' => 1],true);

表示查询出所有id大于1的用户信息
```php
Db::exec('UPDATE users SET name=? where id = ?',['zhangsan',1]);
```

修改id为1的用户的名字为张三

## 删除
```
yao\Db::name('users')->where('id > 10')->delete();
```

删除id大于10的用户。
> 注意：你可以自行安装`medoo`，`think-orm`等数据库操作类库或者使用自带的Db类,该Db类的操作方法大部分需要的是数组类型的参数。


# 事件

事件实现基本代码如下
```
<?php

namespace app\index\event;

class Serve
{
    public function boot()
    {
        echo '1';
    }
}
```


联系邮箱:bigyao@139.com