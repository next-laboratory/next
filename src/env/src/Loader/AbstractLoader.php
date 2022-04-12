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

namespace Max\Env\Loader;

use Max\Env\Contracts\LoaderInterface;
use Max\Utils\Exceptions\FileNotFoundException;
use function file_exists;

abstract class AbstractLoader implements LoaderInterface
{
    /**
     * @var string $path
     */
    protected string $path;

    /**
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @return bool
     */
    public function hasEnv(): bool
    {
        return file_exists($this->path);
    }
}
