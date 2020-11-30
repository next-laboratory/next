<?php

return [
    'debug' => false,
    //开启模板缓存
    'cache' => false,
    //模板缓存路径
    'cache_dir' => ROOT . 'bootstrap' . DS . 'view',
    //模板后缀，使用控制器方法渲染模板时会自动添加该后缀
    'template_suffix' => 'html',
];
