<br>

<p align="center">
<img src="https://raw.githubusercontent.com/marxphp/max/master/public/favicon.ico" width="120" alt="Max">
</p>

<p align="center">轻量 • 简单 • 快速</p>

<p align="center">
<img src="https://img.shields.io/badge/php-%3E%3D7.4-brightgreen">
<img src="https://img.shields.io/badge/license-apache%202-blue">
</p>

`MaxPHP`视图组件，支持`Blade`，可扩展驱动。 可以独立使用!

# 安装

```
composer require max/view
```

# 使用

> Blade引擎支持的语法如下

- {{}}
- {{-- --}}
- {!! !!}
- @extends
- @yield
- @php
- @include
- @if
- @unless
- @empty
- @isset
- @foreach
- @for
- @switch
- @section

> 如果使用`extends` + `yield` + `section`, 务必保证子模板中除了`extends` 之外的所有代码均被`section` 包裹

## 配置文件

安装完成后框架会自动将配置文件`view.php`移动到根包的`config`目录下，如果创建失败，可以手动创建。文件内容如下：

```php
<?php

return [
    'engine'  => '\Max\View\Engine\BladeEngine',
    'config'  => [
        // 模板目录
        'path'        => __DIR__ . '/../views/',
        // 编译和缓存目录
        'compile_dir' => __DIR__ . '/../runtime/cache/views/compile',
        // 模板缓存
        'cache'       => false,
        // 模板后缀
        'suffix'      => '.blade.php',
    ],
];

```

## 使用

```php
// 如果你使用maxphp的容器实例化该类，则不需要传入任何参数，只需要添加相应配置文件即可。
$viewFactory = new ViewFactory($config);
$renderer = $viewFactory->getRenderer();

// 如果你没有使用maxphp, 则需要实例化renderer, 传入对应的驱动
$renderer = new \Max\View\Renderer(new \Max\View\Engine\BladeEngine($options));

$renderer->assign('key', 'value');
$renderer->render('index', ['key2' => 'value2']);
```

## 自定义引擎

自定义引擎必须实现`ViewEngineInterface`接口
