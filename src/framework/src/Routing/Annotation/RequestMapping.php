<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Routing\Annotation;

use Attribute;
use Max\Routing\Contract\MappingInterface;

#[Attribute(Attribute::TARGET_METHOD)]
class RequestMapping implements MappingInterface
{
    /**
     * 默认方法.
     *
     * @var array<int, string>
     */
    public array $methods = ['GET', 'POST', 'HEAD'];

    /**
     * @param string             $path        路径
     * @param array<int, string> $methods     方法
     * @param array              $middlewares 中间件
     */
    public function __construct(
        public string $path = '/',
        array $methods = [],
        public array $middlewares = [],
    ) {
        if (!empty($methods)) {
            $this->methods = $methods;
        }
    }
}
