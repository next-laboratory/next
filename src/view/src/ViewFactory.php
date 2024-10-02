<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Next\View;

use Next\View\Contract\ViewEngineInterface;

class ViewFactory
{
    /**
     * Renderer constructor.
     */
    public function __construct(
        protected ViewEngineInterface $engine
    ) {
    }

    public function getRenderer(): Renderer
    {
        return new Renderer($this->engine);
    }

    public function render(string $template, array $arguments = []): string
    {
        return $this->getRenderer()->render($template, $arguments);
    }
}
