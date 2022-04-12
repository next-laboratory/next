<?php
declare(strict_types=1);

namespace Max\Foundation\View;

use Max\Config\Repository;

class Renderer extends \Max\View\Renderer
{
    /**
     * @param Repository $repository
     *
     * @return static
     */
    public static function __new(Repository $repository)
    {
        $config = $repository->get('view');
        $engine = $config['default'];
        $config = $config['engines'][$engine];
        $engine = $config['engine'];
        return new static(new $engine($config['options']));
    }
}
