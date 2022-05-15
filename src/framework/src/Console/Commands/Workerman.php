<?php

namespace Max\Framework\Console\Commands;

use Max\Console\Commands\Command;
use Max\Di\Context;
use Max\Workerman\Server;

class Workerman extends Command
{
    /**
     * @var string
     */
    protected string $name = 'workerman';

    /**
     * @var string
     */
    protected string $description = 'Manage the workerman server.';

    /**
     * @return void
     */
    public function run()
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
    }
}
