<?php

namespace Max\Framework\Console\Commands;

use Max\Di\Context;
use Max\Workerman\Server;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WorkermanServerCommand extends Command
{
    protected function configure()
    {
        $this->setName('server:workerman')
             ->setDescription('Manage the workerman server.');
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        if (!class_exists('Workerman\Worker')) {
            throw new \RuntimeException('You need to install the workerman using command `composer require workerman/workerman before starting the server.');
        }

        echo <<<EOT
,--.   ,--.                  ,------. ,--.  ,--.,------.  
|   `.'   | ,--,--.,--.  ,--.|  .--. '|  '--'  ||  .--. ' 
|  |'.'|  |' ,-.  | \  `'  / |  '--' ||  .--.  ||  '--' | 
|  |   |  |\ '-'  | /  /.  \ |  | --' |  |  |  ||  | --'  
`--'   `--' `--`--''--'  '--'`--'     `--'  `--'`--' 

EOT;
        /** @var Server $server */
        $server = Context::getContainer()->make(Server::class);
        $server->start();
        return 0;
    }
}
