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

namespace Max\Http\Message\Bags;

use Max\Http\Message\UploadedFile;

class FileBag extends ParameterBag
{
    /**
     * @param string $key
     * @param null   $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null): mixed
    {
        return isset($this->parameters[$key]) ? new UploadedFile($this->parameters[$key]) : $default;
    }

    /**
     * 这个不正确，要重写 TODO
     *
     * @return array
     */
    public function all(): array
    {
        $parameter = [];
        foreach ($this->parameters as $key => $file) {
            $parameter[$key] = new UploadedFile($file);
        }
        return $parameter;
    }
}
