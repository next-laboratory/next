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

namespace Max\Framework\Console\Commands;

use Max\Console\Commands\Command;
use Max\Container\Context;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;

class Server extends Command
{
    /**
     * @var string
     */
    protected string $name = 'server';

    /**
     * @var string
     */
    protected string $description = 'Manage your swoole server.';

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function run()
    {
        echo <<<EOT
,--.   ,--.                  ,------. ,--.  ,--.,------.  
|   `.'   | ,--,--.,--.  ,--.|  .--. '|  '--'  ||  .--. ' 
|  |'.'|  |' ,-.  | \  `'  / |  '--' ||  .--.  ||  '--' | 
|  |   |  |\ '-'  | /  /.  \ |  | --' |  |  |  ||  | --'  
`--'   `--' `--`--''--'  '--'`--'     `--'  `--'`--' 

EOT;

        if (posix_getuid() > 0) {
            exec('whoami', $user);
            $this->output->debug('建议使用root用户启动服务，当前用户：' . $user[0]);
        }
        echo 'PHP:' . PHP_VERSION . PHP_EOL;
        echo 'swoole:' . SWOOLE_VERSION . PHP_EOL;
        $container = Context::getContainer();
        /** @var \Max\Server\Server $server */
        $server = $container->make(\Max\Server\Server::class);
        switch ($this->input->getFirstArgument()) {
            case 'start':
                $this->output->debug('Server started.');
                $server->start();
                break;
            case 'stop':
                $server->stop();
                $this->output->notice('Server stopped!');
                break;
            default:
                $this->output->warning('Please input action \'start\' or \'stop\'');
        }
    }
}
