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

namespace Max\Http\Annotations;

use Attribute;
use Max\Di\Context;
use Max\Di\Contracts\MethodAttribute;
use Max\Routing\Route;
use Max\Routing\RouteCollector;
use Max\Routing\Router;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionMethod;

#[Attribute(Attribute::TARGET_METHOD)]
class RequestMapping implements MethodAttribute
{
    /**
     * 默认方法
     *
     * @var array|string[]
     */
    protected array $methods = ['GET', 'POST', 'HEAD'];

    /**
     * @param string         $path        路径
     * @param array|string[] $methods     方法
     * @param array          $middlewares 中间件
     * @param string         $domain      域名
     */
    public function __construct(
        protected string $path,
        array            $methods = [],
        protected array  $middlewares = [],
        protected string $domain = ''
    )
    {
        if (!empty($methods)) {
            $this->methods = $methods;
        }
    }

    /**
     * @param ReflectionClass  $reflectionClass
     * @param ReflectionMethod $reflectionMethod
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function handle(ReflectionClass $reflectionClass, ReflectionMethod $reflectionMethod)
    {
        $container      = Context::getContainer();
        $router         = $container->get(Router::class);
        $routeCollector = $container->get(RouteCollector::class);
        // TODO 这块有问题，如果没有定义Controller注解，则会只用上一个文件的Controller参数
        $routeCollector->add((new Route(
            $this->methods,
            $router->getPrefix() . $this->path,
            $reflectionClass->getName() . '@' . $reflectionMethod->getName(),
            $router,
            $this->domain,
        ))->middlewares($this->middlewares));
    }
}
