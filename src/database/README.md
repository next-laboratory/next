<br>

<p align="center">
<img src="https://raw.githubusercontent.com/marxphp/max/master/public/favicon.ico" width="120" alt="Max">
</p>

<p align="center">轻量 • 简单 • 快速</p>

<p align="center">
<img src="https://img.shields.io/badge/php-%3E%3D8.0-brightgreen">
<img src="https://img.shields.io/badge/license-apache%202-blue">
</p>

> 简单高效操作数据库，不支持swoole协程

### 使用示例

```php
$db = new \Max\Database\Database(new \Max\Database\PDOConfig());

$query = $db->query(); // 实例化，建立连接
$query->select('select * from users');
$query->selectOne('select * from users limit 1');
$query->delete('delete from users where id = 1');

//...
```

