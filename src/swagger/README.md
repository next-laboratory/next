基于maxphp的session组件，支持文件和缓存，如果使用缓存，不建议使用文件缓存


切换到Cache驱动

```php
'handler'   => 'Max\Session\Handlers\Cache',
'options'   => [
    'ttl'   => 3600,
],
```
