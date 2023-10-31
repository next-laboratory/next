<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Routing;

use Closure;

use function preg_replace_callback;
use function sprintf;
use function trim;

class Route
{
    /**
     * 默认规则.
     */
    protected const DEFAULT_VARIABLE_REGEX = '[^\/]+';

    /**
     * 变量正则.
     */
    protected const VARIABLE_REGEX = '\{\s*([a-zA-Z_][a-zA-Z0-9_-]*)\s*(?::\s*([^{}]*(?:\{(?-1)\}[^{}]*)*))?\}';

    protected string $compiledPath = '';

    /**
     * 路由参数.
     */
    protected array $parameters = [];

    /**
     * 初始化数据.
     */
    public function __construct(
        protected array $methods,
        protected string $path,
        protected Closure|array $action,
        protected array $patterns = [],
        protected array $middlewares = []
    ) {
        $compiledPath       = preg_replace_callback(sprintf('#%s#', self::VARIABLE_REGEX), function ($matches) {
            $name           = $matches[1];
            if (isset($matches[2])) {
                $this->patterns[$name] = $matches[2];
            }
            $this->setParameter($name, null);
            return sprintf('(?P<%s>%s)', $name, $this->getPattern($name));
        }, $this->path      = '/' . trim($this->path, '/'));
        $this->compiledPath = sprintf('#^%s$#iU', $compiledPath);
    }

    /**
     * /**
     * 获取路由参数规则.
     */
    public function getPattern(string $key): string
    {
        return $this->getPatterns()[$key] ?? static::DEFAULT_VARIABLE_REGEX;
    }

    public function getPatterns(): array
    {
        return $this->patterns;
    }

    /**
     * 返回编译后的正则.
     */
    public function getCompiledPath(): string
    {
        return $this->compiledPath;
    }

    /**
     * 设置单个路由参数.
     */
    public function setParameter(string $name, mixed $value): void
    {
        $this->parameters[$name] = $value;
    }

    /**
     * 设置路由参数，全部.
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * 获取单个路由参数.
     */
    public function getParameter(string $name): ?string
    {
        return $this->parameters[$name] ?? null;
    }

    /**
     * 获取全部路由参数.
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * 设置中间件.
     */
    public function middleware(string ...$middlewares): Route
    {
        $this->middlewares = array_unique([...$this->middlewares, ...$middlewares]);

        return $this;
    }

    /**
     * 排除的中间件.
     */
    public function withoutMiddleware(string $middleware): Route
    {
        if (($key = array_search($middleware, $this->middlewares)) !== false) {
            unset($this->middlewares[$key]);
        }

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getAction(): array|string|Closure
    {
        return $this->action;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}
