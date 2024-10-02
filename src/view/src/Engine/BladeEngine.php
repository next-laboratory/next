<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Next\View\Engine;

use Next\View\Contract\ViewEngineInterface;
use Next\View\Engine\Blade\Compiler;
use Next\View\Exception\ViewNotExistException;

class BladeEngine implements ViewEngineInterface
{
    /**
     * @param string $path       视图目录
     * @param string $compileDir 编译文件目录
     * @param string $suffix     视图文件后缀
     * @param bool   $cache      是否缓存
     */
    public function __construct(
        protected string $path,
        protected string $compileDir,
        protected string $suffix = '.blade.php',
        protected bool $cache = false,
    ) {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function isCache(): bool
    {
        return $this->cache;
    }

    public function getSuffix(): string
    {
        return $this->suffix;
    }

    public function getCompileDir(): string
    {
        return $this->compileDir;
    }

    /**
     * @throws ViewNotExistException
     */
    public function render(string $template, array $arguments = [])
    {
        $this->renderView($template, $arguments);
    }

    /**
     * @throws ViewNotExistException
     */
    protected function renderView(): void
    {
        extract(\func_get_arg(1));
        include (new Compiler($this))->compile(\func_get_arg(0));
    }
}
