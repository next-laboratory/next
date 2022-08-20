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
composer require max/validator
```

# 使用

```php
$validator = new \Max\Validator\Validator();
$validator->make([
    'name' => 'maxphp',
], [
    'name' => 'required|max:10',
], [
    'name.required' => 'name是必须的',
    'name.max'      => 'name最大长度10',
])

// 验证失败了
if($validator->fails()){
    // 打印所有错误
    dd($validator->failed());
}
// 获取通过验证的字段列表
$data = $validator->valid();
```

上面的验证会验证所有的, 如果验证失败，你可以获取第一条错误

```php
$validator->errors()->first();
```

如果你需要在一旦出现验证失败就抛出异常

```php
$validator->setThrowable(true);
```

> 官网：https://www.1kmb.com
