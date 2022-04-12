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

namespace Max\View;

use Max\View\Contracts\ViewEngineInterface;


class Renderer
{
    /**
     * Renderer constructor.
     *
     * @param ViewEngineInterface $viewEngine
     */
    public function __construct(protected ViewEngineInterface $viewEngine)
    {
    }

    /**
     * 设置模板目录
     *
     * @param string $path
     *
     * @return void
     */
    public function setPath(string $path)
    {
        $this->viewEngine->setPath($path);
    }

    /**
     * @param string $template
     * @param array  $arguments
     *
     * @return string
     */
    public function render(string $template, array $arguments = []): string
    {
        ob_start();
        echo (string)$this->viewEngine->render($template, $arguments);
        return (string)ob_get_clean();
    }
}
