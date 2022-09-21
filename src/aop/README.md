一款简单Aop实现。支持常驻内存型PHP应用。可以方便接入MaxPHP, Swoole，WebMan等框架。

# 安装

> 环境要求 PHP >= 8.0

```shell
composer require max/aop
```

# 使用，以下以webman为例

## 修改start.php文件

```php
\Max\Aop\Scanner::init(new \Max\Aop\ScannerConfig([
    'cache'      => false,
    'scanDirs'   => [
        BASE_PATH . '/app',
    ],
    'collectors' => [
        \Max\Aop\Collector\AspectCollector::class,
        \Max\Aop\Collector\PropertyAnnotationCollector::class,
    ],
    'runtimeDir' => BASE_PATH . '/runtime',
]));
```

* cache 是否缓存，true时下次启动不会重新生成代理类
* paths 注解扫描路径
* collectors 注解收集器
    - \Max\Aop\Collector\AspectCollector::class 切面收集器，取消后不能使用切面
    - \Max\Aop\Collector\PropertyAnnotationCollector::class 属性注解收集器，取消后不支持属性自动注入
* runtimeDir 运行时，生成的代理类和代理类地图会被缓存到这里

## 编写切面类，实现AspectInterface接口

```php
<?php

namespace App\aspects;

use Closure;
use Max\Aop\JoinPoint;
use Max\Aop\Contract\AspectInterface;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Round implements AspectInterface
{
    public function process(JoinPoint $joinPoint, Closure $next): mixed
    {
        echo 'before';
        $result = $next($joinPoint);
        echo 'after';
        return $result;
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

你也可以使用`AspectConfig`注解类配置要切入的类，例如上面的切面类

```php
<?php

namespace App\aspects;

use Closure;
use Max\Aop\Annotation\AspectConfig;
use Max\Aop\JoinPoint;
use Max\Aop\Contract\AspectInterface;

#[\Attribute(\Attribute::TARGET_METHOD)]
#[AspectConfig('BaconQrCode\Writer', 'writeFile')]
class Round implements AspectInterface
{
    public function process(JoinPoint $joinPoint, Closure $next): mixed
    {
        echo 'before';
        $result = $next($joinPoint);
        echo 'after';
        return $result;
    }
}

```

那么`BaconQrCode\Writer`类的`writeFile`方法将会被切入，该注解可以传递第三个参数数组，作为该切面构造函数的参数

## 启动

```shell
php start.php start
```

打开浏览器打开对应页面

## 控制台输出内容为

```
before--controller--after
```
