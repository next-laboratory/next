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

namespace Max\View\Engines;

use Max\View\Contracts\ViewEngineInterface;


abstract class AbstractEngine implements ViewEngineInterface
{
    /**
     * @var string
     */
    protected string $path;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        foreach ($options as $key => $option) {
            $this->{$key} = $option;
        }
    }

    /**
     * @param string $path
     *
     * @return void
     */
    public function setPath(string $path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
}
