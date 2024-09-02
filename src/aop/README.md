# ！！！ 不能生产使用，仅作为研究用途

一款简单Aop实现。支持常驻内存型PHP应用。可以方便接入nextphp, Swoole，WebMan等框架。

# 环境要求

```
php 8.2
开启passthru函数
```

# 安装

```shell
composer require next/aop
```

# 使用，以下以webman为例

## 修改start.php文件

```php
Aop::init(
    [__DIR__ . '/../app'],
    [
        \Next\Aop\Collector\PropertyAttributeCollector::class,
        \Next\Aop\Collector\AspectCollector::class,
    ],
    __DIR__ . '/../runtime/aop',
);
```

* paths 注解扫描路径
* collectors 注解收集器
    - \Next\Aop\Collector\AspectCollector::class 切面收集器，取消后不能使用切面
    - \Next\Aop\Collector\PropertyAttributeCollector::class 属性注解收集器，取消后不支持属性自动注入
* runtimeDir 运行时，生成的代理类和代理类地图会被缓存到这里

## 编写切面类，实现AspectInterface接口

```php
<?php

namespace App\aspects;

use Closure;
use Next\Aop\JoinPoint;
use Next\Aop\Contract\AspectInterface;

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

use App\Aop\Attribute\Inject;use App\aspects\Round;use support\Request;

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
use Next\Aop\Attribute\AspectConfig;
use Next\Aop\JoinPoint;
use Next\Aop\Contract\AspectInterface;

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

打开浏览器打开~~对应页面~~

## 控制台输出内容为

```
before--controller--after
```