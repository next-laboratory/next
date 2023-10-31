## Session组件，支持File和Redis(不支持协程) Handler，可以自定义SesssionHandler

```php
composer require next/session
```

```php
$sessionHandler = new \Next\Session\Handler\FileHandler();

$session = new \Next\Session\Session($sessionHandler);

$session->start(null); // 如果为null则创建id
$session->set('foo', 'bar');
$session->get('foo');
$session->save();
$session->close();

$sessionId = $session->getId();
```
