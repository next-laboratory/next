一款简单的容器，所有被容器实例化的类以及所有依赖都是单例

# 安装

> 环境要求 PHP >= 8.0

```shell
composer require max/di:dev-master
```

# 使用

获取容器实例，注意不要直接实例化

```php
$container = \Max\Di\Context::getContainer();
$container = container();
```

绑定类和别名

```php
$container->bind(TestInterface::class, Test::class);
```

之后所有容器接口都可以使用TestInterface::class标识来获取Test::class实例

实例化对象, 不保存改对象, 但是会保存所有该类依赖的对象

```php
$container->resolve(Test::class);
```

实例化对象并存储

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
