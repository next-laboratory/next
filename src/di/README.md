一款简单的容器, Aop实现。支持MaxPHP, Swoole，WebMan等框架

# 安装

> 环境要求 PHP >= 8.0

```shell
composer require max/di:dev-master
```

配置如下

```
'cache'      => false,
        'paths'      => [
            BASE_PATH . '/app',
        ],
        'collectors' => [],
        'runtimeDir' => BASE_PATH . '/runtime',
```

# 使用，以下以webman为例

## 修改start.php文件

```php
$loader = require_once __DIR__ . '/vendor/autoload.php';

\Max\Di\Scanner::init($loader, [
    'cache'      => false,
    'paths'      => [
        BASE_PATH . '/app',
    ],
    'collectors' => [],
    'runtimeDir' => BASE_PATH . '/runtime',
]);
```

## 编写切面类，实现AspectInterface接口

```php
<?php

namespace App\aspects;

use Closure;
use Max\Di\Aop\JoinPoint;
use Max\Di\Contracts\AspectInterface;

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
use Max\Di\Annotation\Inject;
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

> 注意上面添加了两个注解，属性和方法注解的作用分别为注入属性和切入方法，可以直接在控制器中打印属性$request发现已经被注入了，切面注解可以有多个，会按照顺序执行。具体实现可以参考这两个类，注意这里的Inject注解并不是从webman容器中获取实例，所以使用的话需要重新定义Inject以保证单例

## 启动

```shell
php start.php start
```

打开浏览器打开对应页面

## 控制台输出内容为`before--controller--after`
