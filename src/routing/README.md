一款简单的路由, 可以独立使用

```php

$router = new Router();

$router->get('index/<name>', function($name) {    // 必选name
    return $name;
});

// 路由分组示例
$router->prefix('api')
    ->middleware('api')
    ->group(function(Router $router) {
        $router->get('/user[/id]', function($id = 0) {   //可选id
            var_dump('user');
        })->middleware('auth')->patterns(['id' => '\d+']);
        $router->middleware('user')->group(function() {
            //
        }
    });
// 路由注册完之后编译路由
RouteCollector::compile();
// 解析路由，返回匹配到的Route对象
$route = RouteCollector::resolve('GET', '/');

var_dump($route);
```

此外还有一系列方法，例如

```php
Route::namespace('App\Http\Controllers')
    ->get('/', 'Index@index');
```

- prefix
- middleware
- namespace
- patterns

如果你使用了MaxPHP，那么可以直接使用路由的门面

```php
use Max\Foundation\Facades\Route;

Route::prefix('/blog/<id>.html')
    ->namespace('App\\Http\\Controllers')
    ->middleware('ai')
    ->group(function(Router $router) {
        $router->get('/', 'Blog@show');
    });
```
