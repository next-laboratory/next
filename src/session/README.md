Session组件，支持File和Redis存储

```php
composer require max/session
```

```php
$sessionManager = \Max\Di\Context::getContainer()->make(\Max\Session\Manager::class);

$session = $sessionManager->create();

$session->start($id); // 如果为null则创建id

$session->set($key, $value);

$session->get($key);

$session->save();

$session->close();
```
