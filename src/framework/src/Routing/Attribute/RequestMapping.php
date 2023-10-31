<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Routing\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RequestMapping
{
    /**
     * @var array<int, string>
     */
    public array $methods = ['GET', 'HEAD', 'POST'];

    /**
     * @param string             $path        路径
     * @param array<int, string> $methods     方法
     * @param array<int, string> $middlewares 中间件
     */
    public function __construct(
        public string $path = '/',
        array         $methods = [],
        public array  $middlewares = [],
    )
    {
        if (!empty($methods)) {
            $this->methods = $methods;
        }
    }
}
