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

class ViewFactory
{
    protected string $engine;

    protected array  $config;

    /**
     * Renderer constructor.
     */
    public function __construct(ConfigInterface $config)
    {
        $config       = $config->get('view');
        $this->engine = $config['engine'];
        $this->config = $config['options'];
    }

    public function getRenderer(): Renderer
    {
        return new Renderer(new $this->engine($this->config));
    }

    public function render(string $template, array $arguments = []): string
    {
        return $this->getRenderer()->render($template, $arguments);
    }
}
