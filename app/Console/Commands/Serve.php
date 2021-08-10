<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Max\Console\Command;

class Serve extends Command
{

    public function configure()
    {
        $this->setName('serve')
            ->addOption('--port', 'Set the port to listen on')
            ->addOption('-p', 'Set the port to listen on')
            ->setDescription('Start the built-in server');
    }

    public function exec()
    {
        $input = $this->input;
        if ($this->input->hasArgument('--help') || $this->input->hasArgument('-H')) {
            exit($this->help());
        }
        if ($input->hasOption('-p')) {
            $port = $input->getOption('-p');
        } else if ($input->hasOption('--port')) {
            $port = $input->getOption('--port');
        } else {
            $port = 8080;
        }
        echo <<<EOT
+------------------------------------------------------+
|                         MaxPHP                       |
|             https://github.com/topyao/max            |
+------------------------------------------------------+
Welcome!                     Press \033[32m【CTRL + C】\033[0m to exit.
Location:                          http://127.0.0.1:{$port}

EOT;
        passthru("php -S 0.0.0.0:{$port} -t public ./server.php");
    }

    public function help()
    {
        $name = str_pad("php max {$this->name} [option]", 33, ' ', STR_PAD_RIGHT);
        return <<<EOT
\033[33m{$name}\033[0m           {$this->getDescription()}
Options:
          -H,        --help                 Show helper  
          -p [8080], --port                 Set the port to listen on 

EOT;

    }

}
