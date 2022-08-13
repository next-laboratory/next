<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\View;

use Max\Config\Contract\ConfigInterface;
use Max\View\Contract\ViewEngineInterface;

class ViewFactory
{
    protected ViewEngineInterface $engine;

    /**
     * Renderer constructor.
     */
    public function __construct(ConfigInterface $config)
    {
        $engine       = $config->get('view.engine');
        $config       = $config->get('view.config', []);
        $this->engine = new $engine($config);
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
