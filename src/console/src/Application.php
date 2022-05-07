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

namespace Max\Console;

use Max\Console\Commands\Command;
use Max\Console\Contracts\InputInterface;
use Max\Console\Contracts\OutputInterface;
use Max\Console\Exceptions\UnNamedCommandException;
use Max\Console\Input\ArgvInput;
use Max\Console\Output\ConsoleOutput;
use Throwable;

class Application
{
    /**
     * 内置命令
     *
     * @var array
     */
    protected array $buildIn = [
        'Max\Console\Commands\HelpCommand',
    ];

    /**
     * @var Command[] 命令容器
     */
    protected array $commands = [];

    /**
     * @throws UnNamedCommandException
     */
    public function __construct()
    {
        foreach ($this->buildIn as $command) {
            $this->add(new $command());
        }
    }

    /**
     * @param Command $command
     *
     * @throws UnNamedCommandException
     */
    public function add(Command $command)
    {
        if ($command->isEnable()) {
            if (!$command->hasName()) {
                throw new UnNamedCommandException(sprintf('Command %s must have a name.', $command::class));
            }
            $command->setApplication($this);
            $this->commands[$command->getName()] = $command;
        }
    }

    /**
     * @return Command[]
     */
    public function all(): array
    {
        return $this->commands;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->commands[$name]);
    }

    /**
     * @param string $name
     *
     * @return Command|null
     */
    public function get(string $name): null|Command
    {
        return $this->commands[$name] ?? null;
    }

    /**
     * @param array $commands
     *
     * @throws UnNamedCommandException
     */
    public function set(array $commands)
    {
        foreach ($commands as $command) {
            $this->add($command);
        }
    }

    /**
     * @param InputInterface|null  $input
     * @param OutputInterface|null $output
     *
     * @return int
     */
    public function run(?InputInterface $input = null, ?OutputInterface $output = null): int
    {
        $input  ??= new ArgvInput();
        $output ??= new ConsoleOutput();
        if (!$name = $input->getCommand()) {
            $name = 'help';
        }
        if (!$this->has($name)) {
            $output->error(sprintf('Command %s is not exist. Run `php max help` to show help.', $name));
            return 2;
        }
        try {
            $this->get($name)?->setInputAndOutput($input, $output)?->run();
            return 0;
        } catch (Throwable $throwable) {
            $output->error($throwable::class . ':' . $throwable->getMessage() . ' at ' . $throwable->getFile() . '+' . $throwable->getLine());
            echo $throwable->getTraceAsString() . PHP_EOL;
            return 1;
        }
    }
}
