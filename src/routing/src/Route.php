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

namespace Max\Routing;

use Closure;
use function preg_replace_callback;
use function sprintf;
use function trim;

class Route
{
    /**
     * 默认规则
     */
    protected const DEFAULT_PATTERN = '[^\/]+';

    /**
     * 路径
     *
     * @var string
     */
    protected string $path;

    /**
     * 路由参数
     *
     * @var array
     */
    protected array $parameters = [];

    /**
     * @var string|null
     */
    protected ?string $regexp = null;

    /**
     * @var array
     */
    protected array $middlewares = [];

    /**
     * @var array
     */
    protected array $patterns = [];

    /**
     * @var string
     */
    protected string $compiledDomain = '';

    /**
     * @var array
     */
    protected array $withoutMiddleware = [];

    /**
     * @var string
     */
    protected string $domain = '';

    /**
     * 初始化数据
     * Route constructor.
     *
     * @param array                $methods
     * @param string               $path
     * @param string|Closure|array $action
     * @param Router               $router
     * @param string               $domain
     */
    public function __construct(
        protected array                $methods,
        string                         $path,
        protected string|Closure|array $action,
        protected Router               $router,
        string                         $domain = '',
    )
    {
        $this->setPath($path)->domain($domain);
    }

    /**
     * @param Router $router
     */
    public function setRouter(Router $router): void
    {
        $this->router = $router;
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function setPath(string $path): Route
    {
        $this->path = '/' . trim($path, '/');

        return $this;
    }

    /**
     * @return string
     */
    public function getCompiledDomain(): string
    {
        return $this->compiledDomain;
    }

    /**
     * 如果设置的uri可编译就编译为正则
     */
    public function compile()
    {
        $regexp = preg_replace_callback('/[<\[](.+?)[>\]]/', function ($matches) {
            [$match, $name] = $matches;
            $hasSlash = '/' === $name[0];
            $newName  = trim($name, '/');
            $nullable = '[' == $match[0];
            $this->setParameter($newName, null);

            return sprintf('(?P<%s>%s%s)%s', $hasSlash ? $newName : $name, $hasSlash ? '/' : '', $this->getPattern($newName), $nullable ? '?' : '');
        }, $this->path);
        if ($regexp !== $this->path) {
            $this->regexp = sprintf('#^%s$#iU', $regexp);
        }
    }

    /**
     * @param string $domain
     *
     * @return $this
     */
    public function domain(string $domain): Route
    {
        if ('' !== $domain) {
            $this->domain         = $domain;
            $this->compiledDomain = '#^' . str_replace(['.', '*',], ['\.', '(.+?)',], $domain) . '$#iU';
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @param array $patterns
     *
     * @return $this
     */
    public function patterns(array $patterns): Route
    {
        $this->patterns = array_merge($this->patterns, $patterns);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRegexp(): ?string
    {
        return $this->regexp;
    }

    /**
     * 获取路由参数规则
     *
     * @param string $key
     *
     * @return string
     */
    public function getPattern(string $key): string
    {
        return $this->getPatterns()[$key] ?? static::DEFAULT_PATTERN;
    }

    /**
     * @return array
     */
    public function getPatterns(): array
    {
        return array_replace($this->router->getPatterns(), $this->patterns);
    }

    /**
     * 设置单个路由参数
     *
     * @param string $name
     * @param        $value
     *
     * @return void
     */
    public function setParameter(string $name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * 设置路由参数，全部
     *
     * @param array $parameters
     *
     * @return void
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * 获取单个路由参数
     *
     * @param string $name
     *
     * @return string|null
     */
    public function getParameter(string $name): ?string
    {
        return $this->parameters[$name] ?? null;
    }

    /**
     * 获取全部路由参数
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * 设置中间件
     *
     * @param string|array $middlewares
     *
     * @return $this
     */
    public function middlewares(string|array $middlewares): Route
    {
        if (is_string($middlewares)) {
            $middlewares = [$middlewares];
        }
        $this->middlewares = $middlewares;

        return $this;
    }

    /**
     * 排除的中间件
     *
     * @param string $middleware
     *
     * @return $this
     */
    public function withoutMiddleware(string $middleware): Route
    {
        $this->withoutMiddleware[] = $middleware;

        return $this;
    }

    /**
     * @param string $method
     *
     * @return $this
     */
    public function addMethod(string $method): static
    {
        if (!in_array($method, $this->methods)) {
            $this->methods[] = $method;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @return array|Closure|string
     */
    public function getAction(): array|string|Closure
    {
        return $this->action;
    }

    /**
     * @return array
     */
    public function getMiddlewares(): array
    {
        $middlewares = array_unique([...$this->router->getMiddlewares(), ...$this->middlewares]);

        return array_diff($middlewares, $this->withoutMiddleware);
    }
}
