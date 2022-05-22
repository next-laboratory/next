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

use Exception;
use Max\Di\Context;
use Max\Swoole\Server;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SwooleServerCommand extends Command
{
    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('server:swoole')
             ->setDescription('Manage the swoole server.')
             ->addArgument('action', InputArgument::REQUIRED, 'start or stop')
             ->setHelp('start/stop');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
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
            $output->writeln('<info>[DEBUG]<info> 建议使用root用户启动服务，当前用户：' . $user[0]);
        }
        echo 'PHP:' . PHP_VERSION . PHP_EOL;
        echo 'swoole:' . SWOOLE_VERSION . PHP_EOL;
        $container = Context::getContainer();
        /** @var Server $server */
        $server = $container->make(Server::class);
        switch ($input->getArgument('action')) {
            case 'start':
                $output->writeln('<info>[DEBU]</info> Server started.');
                $server->start();
                break;
            case 'stop':
                $server->stop();
                $output->writeln('<info>[DEBU]</info> Server stopped.');
        }
    }
}
