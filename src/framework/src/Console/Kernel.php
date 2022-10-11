<?php

namespace Max\Console;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use Symfony\Component\Console\Application;
use function App\config;

class Kernel
{
    /**
     * 注册命令.
     *
     * @var array<int, string> $commands
     */
    protected array $commands = [];

    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     */
    public function run(): void
    {
        $application = new Application('MaxPHP', 'dev');
        $commands    = array_merge($this->commands, CommandCollector::all(), config('config.commands', []));
        foreach ($commands as $command) {
            $application->add(make($command));
        }
        $application->run();
    }
}
