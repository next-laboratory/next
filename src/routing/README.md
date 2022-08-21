# 一款简单的路由

## 初始化

```php
$router = new Router(array $options = [], ?Max\Routing\RouteCollector $routeCollector);
```

如果传递给Router类的路由收集器是null，则内部会自动实例化

## 使用

### 支持GET, POST, PUT, PATCH, DELETE等方法，例如：

```php
$router->get('/', function() {
    // Do something.
});
```

如果需要一个路由注册自定义请求方法，例如：

```php
$router->request('/', function() {
    // Do something.
}, ['GET’, 'OPTIONS']);
```

如果需要一个路由支持所有请求方法，例如：

```php
$router->any('/', function() {
    // Do something.
});
```

### 如果需要注册一个Restful路由，例如：

```php
$router->rest('/book', 'BookController');
```

Restful规则会注册多条路由，上面的规则注册的路由如下：

| Methods | Uri | Action |
| --- | --- | --- |
| GET/HEAD | /book | BookController@index |
| GET/HEAD | /book/{id} | BookController@show |
| POST | /book | BookController@store |
| PUT/PATCH | /book/{id} | BookController@update |
| DELETE | /book/{id} | BookController@delete |

值得注意的是rest方法返回的是一个RestRouter对象，通过该对象，你可以获取到某一个action对应的路由规则，并且注册其他参数，例如：

```php
$rest = $router->rest('/book', 'BookController');
$rest->getShowRoute()->middleware('JWTAuthentication');
```

### 路由支持参数，例如：

```php
// 带参数类型限制, 其中id只有为数字的时候会匹配到
$router->get('/book/{id:\d+}', 'BookController@show');
// 带后缀的路由，注意这里的符号.会被解析成正则元字符的一部分，因此有必要添加反斜线转义
$router->get('/p/{id}\.html', 'CateController@show');
```


### 路由支持分组并且支持分组嵌套，例如：

```php
$router->prefix('api')->group(function(\Max\Routing\Router $router) {
    $router->middleware('Authentication')->group(function(\Max\Routing\Router $router) {
        $router->get('/', function(\Max\Routing\Router $router) {
            // Do something.
        });
        
        $router->where('id', '\d+')->get('/user/{id}', 'UserController@show');
    });
```

上面的规则定义两条路由规则，第一条请求方式为GET，path为`/api`的路由规则，且中间件包含`Authentication`，第二条相相对于第一条还加了参数类型限制，此时id参数只能是数字

> 分组路由前置方法支持`prefix`, `namespace`, `middleware`, `where` 等

对于分组路由，你还可以在闭包中通过引入文件的方式来注册，例如：

```php
$router->group(function(\Max\Routing\Router $router) {
    // 使用引入文件的方式
    require_once './route.php';
});
```

在文件中的路由均使用$router来注册

### 解析路由

解析路由使用路由收集器来完成，如果你没有使用外部，则可以使用Router对象提供的方法获取

```php
$routeCollector = $router->getRouteCollector();
```

解析方法有两个

```php
$route = $routeCollector->resolve('GET', '/'); // 传递请求方式和path
$route = $routeCollector->resolveRequest($request); // 传递一个Psr\Http\Message\ServerRequestInterface对象进行解析
```

解析完成后会返回一个匹配到的路由的克隆对象，该对象中保存的对应变量，如果没有匹配到，则会抛出相应异常
