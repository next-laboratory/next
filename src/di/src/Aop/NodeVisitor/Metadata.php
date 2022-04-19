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

namespace Max\Di\Aop\NodeVisitor;

use Composer\Autoload\ClassLoader;

class Metadata
{
    /**
     * @param string $className      类名
     * @param bool   $hasConstructor 是否有构造函数
     */
    public function __construct(
        public ClassLoader $loader,
        public string      $className,
        public bool        $hasConstructor = false
    )
    {
    }
}
