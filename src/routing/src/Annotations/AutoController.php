<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Routing\Annotations;

use Attribute;
use Max\Routing\Contracts\ControllerInterface;

#[Attribute(Attribute::TARGET_CLASS)]
class AutoController implements ControllerInterface
{
    /**
     * @param string $prefix      前缀
     * @param array  $middlewares 中间件
     * @param array  $methods     请求方法
     * @param array  $patterns    参数规则
     */
    public function __construct(
        public string $prefix = '',
        public array $middlewares = [],
        public array $methods = ['GET', 'POST', 'HEAD'],
        public array $patterns = [],
    ) {
    }
}
