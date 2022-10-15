<br>

<p align="center">
<img src="https://raw.githubusercontent.com/marxphp/max/master/public/favicon.ico" width="120" alt="Max">
</p>

<p align="center">轻量 • 简单 • 快速</p>

<p align="center">
<img src="https://img.shields.io/badge/php-%3E%3D8.0-brightgreen">
<img src="https://img.shields.io/badge/license-apache%202-blue">
</p>

MaxPHP验证器组件

# 安装

```
composer require max/validation
```

# 使用

```php
$validator = new \Max\Validation\Validator(
    ['foo' => 'bar'], 
    ['foo' => 'required|max:10'],
    ['foo.required' => 'foo is required']
);

// 验证
$validator->validate();
// 获取验证后的数据
$data = $validator->valid();

// 验证并返回验证过的数据
$data = $validator->validated();

```

上面的验证会验证所有的, 如果验证失败，你可以获取第一条错误

```php
$validator->errors()->first();
```

默认验证是会在第一个未验证成功的字段后抛出异常，可以给验证器传递第四个参数false来批量验证，验证后可以获取验证通过的字段以及未通过的错误信息
