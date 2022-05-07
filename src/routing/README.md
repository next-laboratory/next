一款简单的路由

```php

$router = new Router();

$router->get('index/<name>', function($name) {    // 必选name
    return $name;
});

// 路由分组示例
$router->prefix('api')
    ->middleware('api')
    ->pattterns(['id' => '\d+'])
    ->group(function(Router $router) {
        $router->get('/user/<id>', function($id = 0) {   //可选id
            var_dump('user');
        })->middleware('auth');
        $router->middleware('user')->group(function() {
            //
        }
    });

// 解析路由，返回匹配到的Route对象
$route = $router->getRouteCollector()->resolve('GET', '/');

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
