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

use function parse_ini_file;

class IniFileLoader extends AbstractLoader
{
    public function export(): array
    {
        return $this->hasEnv() ? parse_ini_file($this->path, true, INI_SCANNER_TYPED) : [];
    }
}
