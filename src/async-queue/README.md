# 一款简单的队列

> 目前使用Redis作为驱动

# 安装

```shell
composer require max/async-queue
```

# 使用

配置文件在`publish/queue.php`中

# 消费消息

```php
<?php

$config = require 'publish/queue.php';
$queue = new Max\AsyncQueue\Queue($config);

$queue->work('default'); // 默认队列 ， 延时队列需要更改为delay

```

将上面的代码保存为`queue.php`，执行`php queue.php`

# 生产消息

## 创建任务类

```php
<?php

// 普通任务，创建一个实现了JobInterface接口的类
class Send extends Max\AsyncQueue\Job\Job {
    public function handle(){
        // Mail::send();
    }
}

// 延时任务，创建一个继承了DelayedJob类的任务
class DelaySend extends Max\AsyncQueue\Job\DelayedJob {
    public function handle(){
        // Mail::send();
    }
}

```

## 入队任务

```php
$config = require 'publish/queue.php';
$queue = new Max\AsyncQueue\Queue($config);

$queue->push(string|array|Job $job);  // 可接受类名，类名和参数的数组，或者任务实例
$queue->later(DelayedJob $job, int $delay = 15); // 延时队列，入队后延时$delay后才执行

```

