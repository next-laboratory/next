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

namespace Max\Http\Contracts;

interface MappingInterface
{
    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @return string
     */
    public function getDomain(): string;

    /**
     * @return array
     */
    public function getMiddlewares(): array;

    /**
     * @return array
     */
    public function getMethods(): array;
}
