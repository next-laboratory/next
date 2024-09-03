<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

use Next\Aop\Collector\AspectCollector;
use Next\Aop\Collector\PropertyAttributeCollector;

/**
 * This file is part of nextphp.
 *
 * @see     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

return [
    'scanDirs'   => [
        __DIR__ . '/app',
    ],
    'collectors' => [
        AspectCollector::class,
        PropertyAttributeCollector::class,
    ],
    'runtimeDir' => __DIR__ . '/runtime/aop',
];
