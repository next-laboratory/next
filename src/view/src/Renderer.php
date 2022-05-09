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

use Max\Config\Contracts\ConfigInterface;
use Max\View\Contracts\ViewEngineInterface;

class Renderer
{
    /**
     * @var ViewEngineInterface|mixed
     */
    protected ViewEngineInterface $engine;

    /**
     * Renderer constructor.
     *
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $config       = $config->get('view');
        $engine       = $config['default'];
        $config       = $config['engines'][$engine];
        $engine       = $config['engine'];
        $this->engine = new $engine($config['options']);
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
        echo (string)$this->engine->render($template, $arguments);
        return (string)ob_get_clean();
    }
}
