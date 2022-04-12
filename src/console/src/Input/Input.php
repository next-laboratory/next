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

use Max\Console\Contracts\InputInterface;

abstract class Input implements InputInterface
{
    /**
     * @var array
     */
    protected array $options = [];

    /**
     * @var array
     */
    protected array $arguments = [];

    /**
     * @return string|null
     */
    public function getFirstArgument(): ?string
    {
        return $this->arguments[0] ?? null;
    }

    /**
     * 判断是否有选项，例如 -c index
     *
     * @param string $option
     *
     * @return bool
     */
    public function hasOption(string $option): bool
    {
        return isset($this->options[$option]);
    }

    /**
     * 根据选项的名字取值
     *
     * @param string      $option
     * @param string|null $default
     *
     * @return ?string
     */
    public function getOption(string $option, ?string $default = null): ?string
    {
        return $this->options[$option] ?? $default;
    }

    /**
     * 获取所有选项
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * 获取所有参数
     *
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * 判断是否有参数, 例如 -H 或者 --help
     *
     * @param string $argument
     *
     * @return bool
     */
    public function hasArgument(string $argument): bool
    {
        $arguments = array_flip($this->arguments);
        return isset($arguments[$argument]);
    }

    /**
     * 获取参数
     *
     * @param string $argument
     *
     * @return string
     */
    public function getArgument(string $argument): string
    {
        return $this->arguments[$argument];
    }
}
