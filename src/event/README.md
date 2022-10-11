# 基于Psr-14的事件

## 如何使用

1. 需要创建一个`Listener`类并实现`\Max\Event\Contract\EventListenerInterface` 中的`listen`和`process`方法。`listen`
   方法要求返回一个数组，数组内的值为该事件监听器监听的事件，
   `process`方法要求传入一个事件对象，该方法不需要返回值，例如

```php
class UserStatusListener implements EventListenerInterface
{
    /**
    * 返回该监听器监听的事件
    * @return string[]
    */
    public function listen():array {
        return [
            \App\Events\UserRegistered::class,
        ];
    }

    /**
    * 触发事件后的处理
    * @param object $event
    */
    public function process(object $event): void
    {
        $event->user = false;
    }
}
```

2. 需要创建一个`Event`类，例如

```php
class UserRegistered
{
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }
}
```

3. 实例化一个`ListenerProvider`, 构造函数需要传入所有监听器对象

```php
$listenerProvider = new ListenerProvider(...[new UserStatusListener]);
```

4. 实例化调度器，给构造函数传入`ListenerProvider`实例

```php
$dispatcher = new \Max\Event\EventDispatcher($listenerProvider);
```

5. 事件调度

```php
$user = User::find(1);

$event = $dispatcher->dispatch(new UserRegistered($user));
```

6. 可终止事件

> 事件实现`StoppableEventInterface`接口中的`isPropagationStopped`方法，并且返回true，则不会触发该事件之后的事件

```php
class UserRegistered implements StoppableEventInterface
{
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function isPropagationStopped() : bool 
    {
        return true;
    }
}
```
