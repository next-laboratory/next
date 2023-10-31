<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Aop\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class AspectConfig
{
    /**
     * @param string       $class   要切入的类名
     * @param array|string $methods 要切入的方法
     * @param array        $params  注解参数
     */
    public function __construct(
        public string $class,
        public string|array $methods = '*',
        public array $params = []
    ) {
    }
}
