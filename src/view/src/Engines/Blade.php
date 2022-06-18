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

namespace Max\View\Engines;

use Max\Utils\Traits\AutoFillProperties;
use Max\View\Contracts\ViewEngineInterface;
use Max\View\Engines\Blade\Compiler;
use Max\View\Exceptions\ViewNotExistException;
use function func_get_arg;

class Blade implements ViewEngineInterface
{
    use AutoFillProperties;

    /**
     * 缓存
     *
     * @var bool
     */
    protected bool $cache = false;

    /**
     * 后缀
     *
     * @var string
     */
    protected string $suffix = '.blade.php';

    /**
     * 编译目录
     *
     * @var string
     */
    protected string $compileDir;

    /**
     * @var string
     */
    protected string $path;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->fillProperties($options);
    }

    /**
     * @param string $path
     *
     * @return void
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return bool
     */
    public function isCache(): bool
    {
        return $this->cache;
    }

    /**
     * @return string
     */
    public function getSuffix(): string
    {
        return $this->suffix;
    }

    /**
     * @return string
     */
    public function getCompileDir(): string
    {
        return $this->compileDir;
    }

    /**
     * @param string $template
     * @param array  $arguments
     *
     * @return void
     * @throws ViewNotExistException
     */
    public function render(string $template, array $arguments = []): void
    {
        $this->renderView($template, $arguments);
    }

    /**
     * @return void
     * @throws ViewNotExistException
     */
    protected function renderView(): void
    {
        extract(func_get_arg(1));
        include (new Compiler($this))->compile(func_get_arg(0));
    }
}
