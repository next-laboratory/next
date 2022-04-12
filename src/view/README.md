<br>

<p align="center">
<img src="https://raw.githubusercontent.com/topyao/max/master/public/favicon.ico" width="120" alt="Max">
</p>

<p align="center">轻量 • 简单 • 快速</p>

<p align="center">
<img src="https://img.shields.io/badge/php-%3E%3D7.4-brightgreen">
<img src="https://img.shields.io/badge/license-apache%202-blue">
</p>

`MaxPHP`视图组件，支持`Blade`，可扩展驱动。 可以独立使用!

# 安装

```
composer require max/view:dev-master
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
    'engine'  => '\Max\View\Engines\Blade',
    'options' => [
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

## 使用

> 如果你使用MaxPHP, 则可以直接注入Renderer实例来使用，否则需要按照下面的方式使用

```php
$engine = config('view.engine');
$renderer = new Renderer(new $engine(config('view.options')));
return $renderer->render('index', ['test' => ['123']]);
```

## 自定义引擎

自定义引擎必须实现`ViewEngineInterface`接口, 将新的引擎实例传递给渲染器即可

> 官网：https://www.1kmb.com
