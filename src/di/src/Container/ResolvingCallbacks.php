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

namespace Max\Di\Container;

use Closure;
use ReflectionClass;
use function is_object;

trait ResolvingCallbacks
{
    /**
     * @var array 全局回调
     */
    protected array $globalResolvingCallbacks = [];

    /**
     * @var array 单个类注册的回调
     */
    protected array $resolvingCallbacks = [];

    /**
     * 解析后回调
     */
    protected function resolving(ReflectionClass $reflectionClass, object $concrete): object
    {
        foreach ($this->getResolvingCallbacks($concrete) as $callback) {
            $callback($this, $reflectionClass, $concrete);
        }

        return $concrete;
    }

    /**
     * 获取回调
     */
    public function getResolvingCallbacks($abstract): array
    {
        $abstract = is_object($abstract) ? $abstract::class : $abstract;

        return $this->globalResolvingCallbacks + ($this->resolvingCallbacks[$abstract] ?? []);
    }

    /**
     * 解析后回调
     */
    public function afterResolving($abstract, ?Closure $callback = null): void
    {
        if ($abstract instanceof Closure && is_null($callback)) {
            $this->globalResolvingCallbacks[] = $abstract;
        } else {
            $this->resolvingCallbacks[$abstract][] = $callback;
        }
    }
}
