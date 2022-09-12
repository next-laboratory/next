<br>

<p align="center">
<img src="https://raw.githubusercontent.com/marxphp/max/master/public/favicon.ico" width="120" alt="Max">
</p>

<p align="center">轻量 • 简单 • 快速</p>

<p align="center">
<img src="https://img.shields.io/badge/php-%3E%3D8.0-brightgreen">
<img src="https://img.shields.io/badge/license-apache%202-blue">
</p>

### bcmath

```php
$b = new \Max\Bc\B(10.123, 3);
$c = $b->add(20.222);
$d = $c->sub(3.333);
$e = $d->div(5.2);
$f = $e->mul(4.1212, 2);
// ...
dump($b->int(), $c->int(), $d->int(), $e->int(), $f->int());
dump($b->string(), $c->string(), $d->string(), $e->string(), $f->string());
```

