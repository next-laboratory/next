<p align="center">
<img src="https://raw.githubusercontent.com/topyao/max-simple/master/public/favicon.ico" width="120" alt="Max">
</p>

<p align="center">轻量 • 简单 • 快速</p>

<p align="center">
<a href="https://github.com/topyao/max/issues"><img src="https://img.shields.io/github/issues/topyao/max" alt=""></a>
<a href="https://github.com/topyao/max"><img src="https://img.shields.io/github/stars/topyao/max" alt=""></a>
<img src="https://img.shields.io/badge/php-%3E%3D8.0-brightgreen" alt="">
<img src="https://img.shields.io/badge/license-apache%202-blue" alt="">
</p>

这是一款组件包，提供了一些web开发常用的组件，而且大部分组件都是可以独立使用的。参与开发可以直接向该包提交代码，将会同步至相应的包。

# 介绍

## 主要组件

- 基于 Psr7 的 [max/http-message](https://github.com/topyao/max-http-message)
- 基于 Psr11 的 [max/di](https://github.com/topyao/max-di)
- 基于 Psr14 的 [max/event](https://github.com/topyao/max-http-message)
- 基于 Psr15 的 [max/http-server](https://github.com/topyao/max-http-server)
- 基于 Psr16 的 [max/cache](https://github.com/topyao/max-cache)，支持 File,Memcached,Redis,APC [可扩展]
- 符合 Psr7 规范的 [max/routing](https://github.com/topyao/max-routing) 路由组件
- 数据库 [max/database](https://github.com/topyao/max-database) 组件，支持连接池
- Session [max/session](https://github.com/topyao/max-session)
- Blade视图组件 [max/view](https://github.com/topyao/max-view)
- 验证器: [max/validator](https://github.com/topyao/max-validator)
- 切面编程 [max/aop](https://github.com/topyao/max-aop)

# 安装

## 要求

```
PHP >= 8.0
```

> 如果你没有使用过composer 可以先了解一下这块的知识

## 使用Composer安装

```
composer create-project max/simple
```

这行命令会在你命令执行目录安装框架，使用下面的命令启动服务

# 启动服务

> swoole服务

```php
php bin/swoole.php   // 异步模式
php bin/swooleco.php // 协程模式
```

> workerman服务

```php
php bin/workerman.php start
```

> 内置服务

```php
php bin/cli-server.php
```

> FPM模式，将请求指向public/index.php即可

框架强制路由，框架对数据类型比较敏感，例如在该设置为`true`时候不要设置`1`。否则会报错。

> 使用swoole/workerman支持注解，AOP等特性， FPM模式可以直接卸载AOP包。

## Nginx代理

如果你使用swoole/workerman，强烈建议添加代理

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

框架强制路由，框架对数据类型比较敏感，例如在该设置为`true`时候不要设置`1`。否则会报错。

## 目录结构

- app 应用目录
  - Http
    - Controllers 控制器目录
  - Middlewares 中间件目录
  - Kernel.php http内核
  - Events 事件
  - Listeners 监听器
  - Exceptions 异常相关
  - Model 模型目录
  - Bootstrap.php
  - helpers.php 辅助函数
- bin
  - cli.php 命令行
  - swoole.php swoole服务（异步风格）
  - swooleco.php swoole服务 （协程风格）
  - cli-server.php 命令行 （内置服务）
  - workerman.php 命令行 （workerman服务）
- config 配置文件目录
  - app.php 应用配置文件
  - cache.php 缓存配置文件
  - logger.php 日志配置文件
  - redis.php Redis配置文件
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

## 简单使用

> swoole/swooleco/workerman下可以使用注解定义

```php
<?php

namespace App\Controllers;

use App\Http\Response;
use Max\Routing\Annotation\Controller;
use Max\Routing\Annotation\GetMapping;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[Controller(prefix: '/')]
class IndexController
{
    #[GetMapping(path: '/<id>')]
    public function index(ServerRequestInterface $request, $id): ResponseInterface
    {
        return (new Response())->HTML('Hello, ' . $request->get('name', 'MaxPHP!'));
    }
}

```

如上请求`0.0.0.0:8080/1` 会指向`index`方法，控制器方法接收`$request`参数和路由参数，如上路由中的`<id>`的值会被传递给`$id`，控制器方法必须返回`ResponseInterface`实例。

> FPM或内置服务下不能使用注解

路由定义在`App\Kernel`类的`map`方法中定义

```php
$router->middleware(TestMiddleware::class)->group(function(Router $router) {
    $router->get('/', [IndexController::class, 'index']);
    $router->get('/test', function(\Psr\Http\Message\ServerRequestInterface $request) {
        return (new \App\Http\Response())->HTML('new');
    });
});
```

# 贡献一览

[![Contributor over time](https://contributor-overtime-api.apiseven.com/contributors-svg?chart=contributorOverTime&repo=topyao/max,topyao/max-routing,topyao/max-session,topyao/max-view,topyao/max-di,topyao/max-cache,topyao/max-simple,topyao/max-http-message,topyao/max-http-server,topyao/max-event,topyao/max-config,topyao/max-aop,topyao/max-database,topyao/max-log,topyao/max-redis,topyao/max-validator)](https://contributor-overtime-api.apiseven.com/contributors-svg?chart=contributorOverTime&repo=topyao/max,topyao/max-routing,topyao/max-session,topyao/max-view,topyao/max-di,topyao/max-cache,topyao/max-simple,topyao/max-http-message,topyao/max-http-server,topyao/max-event,topyao/max-config,topyao/max-aop,topyao/max-database,topyao/max-log,topyao/max-redis,topyao/max-validator)

欢迎有兴趣的朋友参与开发
