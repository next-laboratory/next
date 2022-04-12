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
     * @var array
     */
    protected array $globalResolvingCallbacks = [];

    /**
     * @var array
     */
    protected array $resolvingCallbacks = [];

    /**
     * 解析后回调
     *
     * @param ReflectionClass $reflectionClass
     * @param object          $concrete
     *
     * @return object
     */
    protected function resolving(ReflectionClass $reflectionClass, object $concrete): object
    {
        foreach ($this->getResolvingCallbacks($concrete) as $callback) {
            $callback($this, $reflectionClass, $concrete);
        }

        return $concrete;
    }

    /**
     * @param $abstract
     *
     * @return array
     */
    public function getResolvingCallbacks($abstract): array
    {
        $abstract = is_object($abstract) ? $abstract::class : $abstract;

        return $this->globalResolvingCallbacks + ($this->resolvingCallbacks[$abstract] ?? []);
    }

    /**
     * 解析后回调
     *
     * @param               $abstract
     * @param Closure|null  $callback
     */
    public function afterResolving($abstract, ?Closure $callback = null)
    {
        if ($abstract instanceof Closure && is_null($callback)) {
            $this->globalResolvingCallbacks[] = $abstract;
        } else {
            $this->resolvingCallbacks[$abstract][] = $callback;
        }
    }
}
