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

namespace Max\Console\Input;

class ArgvInput extends Input
{
    /**
     * @param array|null $argv
     */
    public function __construct(array $argv = null)
    {
        $argv = $argv ?? $_SERVER['argv'] ?? [];
        array_shift($argv);
        $argv = implode(' ', $argv);
        preg_match_all('/(-{1,2}[\w-]+)\s+(?!-)([\/\w\.\-]+)/', $argv, $matches);
        if (!empty($matches)) {
            $this->options   = array_combine($matches[1], $matches[2]);
            $this->arguments = array_filter(explode(' ', str_replace($matches[0], '', $argv)));
        }
    }
}

