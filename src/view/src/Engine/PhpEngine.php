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

class PhpEngine implements ViewEngineInterface
{
    use AutoFillProperties;

    protected string $suffix = '.php';

    protected string $path;

    public function __construct(array $config)
    {
        $this->fillProperties($config);
    }

    public function render(string $template, array $arguments = []): void
    {
        $this->renderView($template, $arguments);
    }

    protected function renderView(): void
    {
        extract(func_get_arg(1));
        include $this->findViewFile(func_get_arg(0));
    }

    protected function findViewFile(string $view): string
    {
        return sprintf('%s/%s%s', rtrim($this->path, '/'), trim($view, '/'), $this->suffix);
    }
}
