> 要求：php >= 7.4
访问目录为`public`目录

安装方法 `composer create-project chengyao/yao`

建议使用git拉取源码，因为composer安装的可能不是最新版
https://gitee.com/cheng-yao/yao
如果使用git安装，需要使用composer install 安装依赖

使用 `php yao serve [-p 8080]` 运行程序（注意当路由中存在后缀例如.html时可能会出现404，这时应该考虑使用apache）
> 框架强制路由，所以在编写控制器前应该先定义路由规则，如果你的环境是windows需要修改public/.htaccess中的RewriteRule或者nginx伪静态规则，在index.php后面加上?。框架对数据类型比较敏感，例如在该设置为true时候不要设置1。否则会报404。

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


# 一、配置文件
## ENV
在开发环境下大多数配置都可以通过.env文件获取，而且默认为从.env文件获取，线上环境需要删除.env文件或者将配置中的env去掉，例如在`app.php`中有这样`'debug' => env('app.debug', false),`一个配置，我们可以更改为`'debug' => false,`
.env文件可以使用节，例如：
```
[APP]
DEBUG=true
AUTO_START=true
```
其中app就是一节，获取`DEBUG`配置可以使用`env('app.debug')`或者`\yao\facade\Env::get('app.debug');`

## config
配置文件是位于`config`目录下，以`.php`结尾的文件。

使用`yao\Config::get()`获取所有配置，可以传入一个参数例如`app`,则会获取`app.php`文件中的配置，传入`app.auto_start` 则获取`app`中的`auto_start`参数的值

### 自定义配置文件
如果需要自定义一个配置文件，可以在`/config`目录下新建例如`alipay.php`文件并返回一个数组。使用`Config::get('alipay.param')`获取配置的值

>可以使用辅助函数config() ，例如config('app.debug')


# 二、路由定义方法
## 在`route/route.php`中添加
> Route::请求方式('路由地址','模块/控制器/方法');

注意：需要引入`yao\facade\Route`类

>请求方式可以是get,post,put,delete,patch请求类型

还可以定义多个类型的路由，例如：（这里的/非必须，但是建议加上）
>Route::rule('/', 'index/index/index', ['get', 'post']);

第三个参数传入请求方式数组，可以为空，为空默认为get和post

## 路由定义支持闭包

```
Route::get('index',function(){
       return view('模板文件');
});
```

注意：这里使用到了view助手函数，可以传入第三个参数，表示渲染基于ROOT常量路径的模板。

## 路由定义支持数组
> 例如 Route::get('index',['\app\index\controller\Index','index']);
表示路由到`\app\index\controller\Index`控制器的`index`方法

## 路由定义支持正则表达式
例如：`Route::get('/article/(\d+).html', 'index/article/read');` 会匹配`/article/数字.html`的请求地址，并且会给`read`方法传入一个参数，该参数不能用其他请求方法获取，需要传入的参数要在正则表达式中用括号括起来，可以传入多个参数,传入顺序按照preg_match之后的match数组去掉第一个之后的顺序传入

注意：正则路由中的正则不需要添加定界符。路由匹配遵循最先原则，即在route.php中出现的顺序，即匹配到之后就停止匹配


如果需要将路由分文件存储，只需要将文件引入到route.php即可

开发环境下可以设置`app.php`或者`.env`配置文件中的`debug`为`true`，这样就可以看到错误错误的详细信息了。


# 三、请求
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

# 四、验证器

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


# 五、控制器
假如我定义了一个`Route::get('/','index/index/index');`的路由
如果需要编写控制器代码，就需要编写`/app/index/controller`目录下的`Index.php`控制器下的`index`方法



# 六、模板（smarty）
模板引擎使用了`smarty`
模板目录位于`/app/模块/view` 目录，比如我在`/app/index/view/index`目录下有一个`index.html`的模板，我可以在控制器方法中使用`return view('index/index')`渲染模板，该方法可以传入第二个数组参数用来给模板赋值，例如
>view('index',['data'=>$data]);

或者使用facade的`yao\facade\View::fetch('template',$params);`

其中的`index`对应`app/index/view`目录下的`index.html`文件。
模板后缀可以在`/config/view`中修改`template_suffix`
配置文件中还可以修改默认的模板变量左右分隔符和缓存。默认缓存为关闭

> 你可以使用composer安装你喜欢的模板引擎

# 七、facade

加入了facade，可以在任何可以composer自动加载的位置创建类继承yao\Facade类，就可以实现静态代理
```
class View extends Facade
{
    protected static function getFacadeClass()
    {
        return \yao\View::class;
    }
}
```
>注意: facade默认实例化对象都不是单例的。如果需要使用单例，可以加入`protected static $singleInstance = true;`，当然仅仅是在你的请求的页面中使用该类的方法全部为facade的时候才是单例的。

# 八、数据库（目前只支持mysql）
## 新增
> db('users')->insert(['name' => 'username','age' => 28]);
## 删除
> db('users')->where(['id' => 1])->delete();
## 查询(查询到的是数据集对象，可以使用toArray或者toJson获取)
> yao\Db::name('表名')->field('字段')->where([条件])->limit(1,3)->find()/select();
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
> Db::exec('UPDATE users SET name=? where id = ?',['zhangsan',1]);

修改id为1的用户的名字为张三

## 删除
> yao\Db::name('users')->where('id > 10')->delete();

删除id大于10的用户。
> 注意：你可以自行安装`medoo`，`think-orm`等数据库操作类库或者使用自带的Db类,该Db类的操作方法大部分需要的是数组类型的参数。

联系邮箱:bigyao@139.com