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

use Exception;
use Max\View\Engines\Blade\Compiler;
use function func_get_arg;


class Blade extends AbstractEngine
{
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
     * 渲染
     *
     * @param string $template
     * @param array  $arguments
     *
     * @throws Exception
     */
    public function render(string $template, array $arguments = [])
    {
        $this->renderView($template, $arguments);
    }

    /**
     * 渲染模板
     *
     * @throws Exception
     */
    protected function renderView()
    {
        extract(func_get_arg(1));
        include $this->getCompiler()->compile(func_get_arg(0));
    }

    /**
     * 返回一个新的compiler
     *
     * @return Compiler
     */
    protected function getCompiler(): Compiler
    {
        return new Compiler($this);
    }
}
