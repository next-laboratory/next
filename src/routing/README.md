一款简单的路由

```php

$router = new Router();

$router->get('index/{name}', function($name) {
    return $name;
});

// 路由分组示例
$router->prefix('api')
    ->middleware('api')
    ->pattterns(['id' => '\d+'])
    ->group(function(Router $router) {
        $router->get('/user/{id}', function($id = 0) { 
            var_dump('user');
        })->middleware('auth');
        $router->middleware('user')->group(function() {
            // 使用引入文件的方式
            require_once './route.php';
        });
    });

// 带参数类型限制, 其中id只有为数字的时候会匹配到
$router->get('/book/{id:\d+}', 'BookController@show');
// 带后缀的路由，注意这里的符号.会被解析成正则元字符的一部分，因此有必要添加反斜线转义
$router->get('/p/{id}\.html', 'CateController@show');

// 解析路由，返回匹配到的Route对象
$route = $router->getRouteCollector()->resolve('GET', '/');
// 或者你可以传入一个ServerRequestInterface对象
$route = $router->getRouteCollector()->resolveRequest($request);

var_dump($route);
```

此外还有一系列方法，例如

```php
$router->namespace('App\Http\Controllers')->get('/', 'Index::index');
```

- prefix
- middleware
- namespace
- patterns
