<?php

declare (strict_types=1);

/**
 * This file is part of the Max package.
 *
 * (c) Cheng Yao <987861463@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Max\Env\Env;

if (false === function_exists('env')) {
    /**
     * @param string $key
     * @param        $default
     *
     * @return array|ArrayAccess|mixed
     */
    function env(string $key, $default = null): mixed
    {
        return Env::get($key, $default);
    }
}
