<?php

namespace App\Console\Commands;

use Max\Console\Command;
use Max\Console\Style;

class Help extends Command
{

    protected $name = 'help';

    protected $description = 'Show commands list';

    public function exec()
    {
        $commands = $this->app->console->getAllCommands();
        $this->writeLine('Usage:', Style::COLOR_AZURE);
        foreach ($commands as $name => $command) {
            $command = $this->app->make($command, [], true);
            $helper  = $command->help();
            if (empty($helper)) {
                $name = str_pad($name, 36, ' ', STR_PAD_RIGHT);
                $this->writeLine("php max {$name}\033[0m{$command->getDescription()}", Style::COLOR_YELLOW);
            }
            $this->writeLine($command->help() ?? '');
        }
    }

}
