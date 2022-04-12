<?php
declare(strict_types=1);

return [
    'default' => 'file',
    'stores'  => [
        //文件缓存
        'file'      => [
            'handler' => 'Max\Cache\Handlers\File',
            'options' => [
                'path' => __DIR__ . '/../runtime/cache/app',
            ],
        ],
        // redis缓存
        'redis'     => [
            'handler' => 'Max\Cache\Handlers\Redis',
            'options' => [],
        ],
        //memcached缓存
        'memcached' => [
            'handler' => 'Max\Cache\Handlers\Memcached',
            'options' => [
                'host' => '127.0.0.1', //主机
                'port' => 11211        //端口
            ],
        ]
    ],
];
