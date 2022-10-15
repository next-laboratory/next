<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

return [
    'cache'      => false,
    'scanDirs'   => [
        __DIR__ . '/app',
    ],
    'collectors' => [
        \Max\Aop\Collector\AspectCollector::class,
        \Max\Aop\Collector\PropertyAttributeCollector::class,
    ],
    'runtimeDir' => __DIR__ . '/runtime/aop',
];
