var-dumper 适配包，用来将变量打印到浏览器

# 安装

```shell
composer require max/var-dumper
```

# 使用

## hyperf

修改`app/config/autoload/exceptions.php`

```php
<?php

declare(strict_types=1);

return [
    'handler' => [
        'http' => [
            Max\VarDumper\Adapter\HyperfDumperHandler::class,
            Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler::class,
            App\Exception\Handler\AppExceptionHandler::class,
        ],
    ],
];

```

## webman

建立新的异常处理类

```php
<?php

namespace App;

use Max\VarDumper\Dumper;
use Max\VarDumper\DumperHandler;
use support\exception\Handler;
use Throwable;
use Webman\Http\Request;
use Webman\Http\Response;

class ExceptionHandler extends Handler
{
    use DumperHandler;

    public function render(Request $request, Throwable $exception): Response
    {
        if ($exception instanceof Dumper) {
            return \response(self::convertToHtml($exception));
        }
        return parent::render($request, $exception);
    }
}

```

修改config/exception.php

```php
return [
    '' => \App\ExceptionHandler::class,
];
```

## 其他框架可参考webman配置，引入AbortHandler，将异常转为响应即可

# 打印

```php
d($request);
```
