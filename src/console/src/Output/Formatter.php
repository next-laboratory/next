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

namespace Max\Console\Output;

class Formatter
{
    /**
     * 前景色
     *
     * @var int[][]
     */
    private static array $foregroundColors = [
        'black'   => ['set' => 30, 'unset' => 39],
        'red'     => ['set' => 31, 'unset' => 39],
        'green'   => ['set' => 32, 'unset' => 39],
        'yellow'  => ['set' => 33, 'unset' => 39],
        'blue'    => ['set' => 34, 'unset' => 39],
        'magenta' => ['set' => 35, 'unset' => 39],
        'cyan'    => ['set' => 36, 'unset' => 39],
        'white'   => ['set' => 37, 'unset' => 39],
        'default' => ['set' => 39, 'unset' => 39],
    ];

    /**
     * 背景色
     *
     * @var int[][]
     */
    private static array $backgroundColors = [
        'black'   => ['set' => 40, 'unset' => 49],
        'red'     => ['set' => 41, 'unset' => 49],
        'green'   => ['set' => 42, 'unset' => 49],
        'yellow'  => ['set' => 43, 'unset' => 49],
        'blue'    => ['set' => 44, 'unset' => 49],
        'magenta' => ['set' => 45, 'unset' => 49],
        'cyan'    => ['set' => 46, 'unset' => 49],
        'white'   => ['set' => 47, 'unset' => 49],
        'default' => ['set' => 49, 'unset' => 49],
    ];

    /**
     * 选项
     *
     * @var int[][]
     */
    private static array $options = [
        'bold'       => ['set' => 1, 'unset' => 22],
        'underscore' => ['set' => 4, 'unset' => 24],
        'blink'      => ['set' => 5, 'unset' => 25],
        'reverse'    => ['set' => 7, 'unset' => 27],
        'conceal'    => ['set' => 8, 'unset' => 28],
    ];

    /**
     * 前景色
     *
     * @var array|null
     */
    protected ?array $foreground = null;

    /**
     * 背景色
     *
     * @var array|null
     */
    protected ?array $background = null;

    /**
     * 选项
     *
     * @var array|null
     */
    protected ?array $option = null;

    /**
     * @param string|null $foreground
     * @param string|null $background
     * @param string|null $option
     */
    public function __construct(?string $foreground = null, ?string $background = null, ?string $option = null)
    {
        isset($foreground) && $this->setForeground($foreground);
        isset($background) && $this->setBackground($background);
        isset($option) && $this->setOption($option);
    }

    /**
     * @param string $foreground
     *
     * @return Formatter
     */
    public function setForeground(string $foreground): static
    {
        $this->foreground = self::$foregroundColors[$foreground];

        return $this;
    }

    /**
     * @param string $background
     *
     * @return Formatter
     */
    public function setBackground(string $background): static
    {
        $this->background = self::$backgroundColors[$background];

        return $this;
    }

    /**
     * @param string $option
     *
     * @return Formatter
     */
    public function setOption(string $option): static
    {
        $this->option = self::$options[$option];

        return $this;
    }

    /**
     * @param $text
     *
     * @return string
     */
    public function apply($text): string
    {
        $setCodes = $unsetCodes = [];

        if (null !== $this->foreground) {
            $setCodes[]   = $this->foreground['set'];
            $unsetCodes[] = $this->foreground['unset'];
        }
        if (null !== $this->background) {
            $setCodes[]   = $this->background['set'];
            $unsetCodes[] = $this->background['unset'];
        }
        if (count(static::$options)) {
            foreach (static::$options as $option) {
                $setCodes[]   = $option['set'];
                $unsetCodes[] = $option['unset'];
            }
        }

        if (0 === count($setCodes)) {
            return $text;
        }

        return sprintf("\033[%sm%s\033[%sm", implode(';', $setCodes), $text, implode(';', $unsetCodes));
    }
}
