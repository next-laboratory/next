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

class Renderer
{
    /**
     * @var mixed|ViewEngineInterface
     */
    protected ViewEngineInterface $engine;

    /**
     * Renderer constructor.
     */
    public function __construct(ConfigInterface $config)
    {
        $config       = $config->get('view');
        $engine       = $config['default'];
        $config       = $config['engines'][$engine];
        $engine       = $config['engine'];
        $this->engine = new $engine($config['options']);
    }

    public function render(string $template, array $arguments = []): string
    {
        ob_start();
        echo (string) $this->engine->render($template, $arguments);
        return (string) ob_get_clean();
    }
}
