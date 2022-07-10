<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Aop\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class AspectConfig
{
    /**
     * @param string $class  要切入的类名
     * @param string $method 要切入的方法
     * @param array  $params 注解参数
     */
    public function __construct(
        public string $class,
        public string $method = '*',
        public array $params = []
    ) {
    }
}
