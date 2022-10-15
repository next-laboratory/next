这是一款独立的组件包，可以使用点语法来获取加载的配置

```php
$repository = new \Max\Config\Repository();

// 扫描该路径下的所有PHP文件
$repository->scan([__DIR__]);

// 加载某一个文件
$repository->load([__DIR__.'./config/app.php']);

// 获取配置
$repository->get('app.debug');
```

> 注意：$repository示例应该保持单例，避免重复加载，加载配置的规则如下

例如对于app.php配置文件内容如下

```php
return [
    'debug' => true,
];
```

加载后会按照文件名作为外层数组的键，因此获取配置需要使用`$repository->get('app.debug'')`，支持使用点语法。
