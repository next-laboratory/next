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

use Max\Console\Contracts\OutputInterface;

class ConsoleOutput implements OutputInterface
{
    /**
     * @param $content
     *
     * @return void
     */
    public function warning($content)
    {
        echo (new Formatter())
                ->setBackground('default')
                ->setForeground('yellow')
                ->apply('[WARNING]') . $content . PHP_EOL;
    }

    /**
     * @param $content
     *
     * @return void
     */
    public function debug(string $content)
    {
        echo (new Formatter())
                ->setBackground('default')
                ->setForeground('green')
                ->apply('[DEBUG]') . $content . PHP_EOL;
    }

    /**
     * @param $content
     *
     * @return void
     */
    public function error($content)
    {
        echo (new Formatter())
                ->setBackground('default')
                ->setForeground('red')
                ->apply('[ERROR]') . $content . PHP_EOL;
    }

    /**
     * @param string $content
     *
     * @return void
     */
    public function notice(string $content)
    {
        echo (new Formatter())
                ->setForeground('cyan')
                ->apply('[NOTICE]') . $content . PHP_EOL;
    }

    /**
     * @param string $message
     * @param bool   $newLine
     */
    public function write(string $message, bool $newLine = false)
    {
        echo $message;
        if ($newLine) {
            echo PHP_EOL;
        }
    }

    /**
     * @param string $message
     */
    public function writeLine(string $message)
    {
        $this->write($message, true);
    }
}
