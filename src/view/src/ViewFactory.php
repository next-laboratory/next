<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\View;

use Max\Config\Contracts\ConfigInterface;
use Max\View\Contracts\ViewEngineInterface;

class ViewFactory
{
    protected ViewEngineInterface $engine;

    /**
     * Renderer constructor.
     */
    public function __construct(ConfigInterface $config)
    {
        $engine       = $config->get('view.engine');
        $options      = $config->get('view.options', []);
        $this->engine = new $engine($options);
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
