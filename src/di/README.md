一款简单的容器，所有被容器实例化的类以及所有依赖都是单例

# 安装

> 环境要求 PHP >= 8.0

```shell
composer require next/di
```

# 使用

获取容器实例，注意不要直接实例化

```php
$container = \Next\Di\Context::getContainer();
$container = container();
```

绑定类和别名，之后所有容器接口都可以使用TestInterface::class标识来获取Test::class实例

```php
$container->bind(TestInterface::class, Test::class);
```

实例化

```php
$container->make(Test::class);
```

获取对象

```php
$container->get(Test::class);
```

调用方法
```php
$conatiner->call(callable $callable, array $arguments = []);
```

> 注意：所有需要传参的api均需要关联数组，数组的键为参数的名字
