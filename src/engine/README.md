```php
<?php

use Max\Pipeline\Context;

$context = new Context();
$context->use(function (Context $context) {
    var_dump('2');
    $context->next();
    var_dump('3');
});

$context->use(function (Context $context) {
    var_dump('4');
    $context->next();
    var_dump('5');
});

$context->final(function (Context $context) {
    var_dump('1');
})->next();
```

上面代码会输出。

```
string(1) "2"
string(1) "4"
string(1) "1"
string(1) "5"
string(1) "3"
```
