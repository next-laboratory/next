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
use Max\Http\Contracts\MappingInterface;

#[Attribute(Attribute::TARGET_METHOD)]
class RequestMapping implements MappingInterface
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
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @return array|string[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }
}
