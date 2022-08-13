<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\View\Engine;

use Max\Utils\Traits\AutoFillProperties;
use Max\View\Contract\ViewEngineInterface;
use Max\View\Engine\Blade\Compiler;
use Max\View\Exception\ViewNotExistException;

use function func_get_arg;

class BladeEngine implements ViewEngineInterface
{
    use AutoFillProperties;

    /**
     * 缓存.
     */
    protected bool $cache = false;

    /**
     * 后缀
     */
    protected string $suffix = '.blade.php';

    /**
     * 编译目录.
     */
    protected string $compileDir;

    protected string $path;

    public function __construct(array $config)
    {
        $this->fillProperties($config);
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
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
        extract(func_get_arg(1));
        include (new Compiler($this))->compile(func_get_arg(0));
    }
}
