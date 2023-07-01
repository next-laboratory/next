## Session组件，支持File和Redis(不支持协程) Handler，可以自定义SesssionHandler

```php
composer require max/session
```

```php
$sessionHandler = new \Max\Session\Handler\FileHandler();

$session = new \Max\Session\Session($sessionHandler);

$session->start(null); // 如果为null则创建id
$session->set('foo', 'bar');
$session->get('foo');
$session->save();
$session->close();

$sessionId = $session->getId();
```
