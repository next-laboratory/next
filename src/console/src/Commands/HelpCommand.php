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

namespace Max\Console\Commands;

use Max\Console\Output\ConsoleOutput;
use Max\Console\Output\Formatter;

class HelpCommand extends Command
{
    /**
     * @var string
     */
    protected string $name = 'help';

    /**
     * @var string
     */
    protected string $description = 'Show help.';

    /**
     * @return void
     */
    public function run()
    {
        /** @var ConsoleOutput $output */
        echo (new Formatter())->setForeground('blue')->apply('Usage:') . PHP_EOL;
        foreach ($this->getApplication()->all() as $name => $command) {
            $name = str_pad($name, 36, ' ', STR_PAD_RIGHT);
            echo (new Formatter())->setForeground('yellow')->apply('php max ' . $name) . $command->getDescription() . PHP_EOL;
            if ($help = $command->getHelp()) {
                echo (new Formatter())->setForeground('cyan')->apply('Options:') . PHP_EOL;
                echo $help . PHP_EOL;
            }
        }
    }
}
