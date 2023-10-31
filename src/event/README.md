# 基于Psr-14的事件

## 如何使用

### 创建监听器

```php
use Next\Event\Contract\EventListenerInterface;

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
    
    /**
    * 监听器优先级
    * 如果一个事件被多个监听器监听，那么执行顺序可以通过该方法调整
    * 优先级数字越大，优先级越高，越先执行
    * @return int
    */
    public function getPriority(): int 
    {
        return 0;
    }
}
```

> 如果你不需要调整优先级，可以直接继承`Next\Event\EventListener`类

### 需要创建一个事件类

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

### 实例化`ListenerProvider`, 使用`addListener`添加监听器

```php
$listenerProvider = new ListenerProvider();
$listenerProvider->addListener(new UserStatusListener());
```

### 实例化调度器，给构造函数传入`ListenerProvider`实例

```php
$dispatcher = new \Next\Event\EventDispatcher($listenerProvider);
```

### 事件调度

```php
$user = User::find(1);

$event = $dispatcher->dispatch(new UserRegistered($user));
```

## 可终止事件

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
