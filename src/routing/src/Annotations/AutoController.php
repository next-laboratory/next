<?php

declare(strict_types=1);

/**
 * This file is part of the Max package.
 *
 * (c) Cheng Yao <987861463@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Max\Routing\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class AutoController
{
    /**
     * @param string $prefix      前缀
     * @param array  $middlewares 中间件
     * @param array  $methods     请求方法
     */
    public function __construct(
        public string $prefix = '',
        public array  $middlewares = [],
        public array  $methods = ['GET', 'POST', 'HEAD']
    )
    {
    }
}
