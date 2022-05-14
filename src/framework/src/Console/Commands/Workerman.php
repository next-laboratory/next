<?php

namespace Max\Framework\Console\Commands;

use Max\Console\Commands\Command;
use Max\Di\Context;
use Max\Http\Message\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Workerman\Protocols\Http\Request;
use Workerman\Worker;

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

        $worker = new Worker('http://0.0.0.0:8989');

        $worker->onMessage = function (\Workerman\Connection\TcpConnection $tcpConnection, \Workerman\Protocols\Http\Request $request) {
            try {
                $psrRequest = ServerRequest::createFromWorkermanRequest($request);
                \Max\Context\Context::put(ServerRequestInterface::class, $psrRequest);
                \Max\Context\Context::put(Request::class, $request);
                \Max\Context\Context::put(\Psr\Http\Message\ResponseInterface::class, new \Max\Http\Message\Response());
                $requestHandler = Context::getContainer()->make(\Psr\Http\Server\RequestHandlerInterface::class);
                $psr7Response = $requestHandler->handle(Context::getContainer()->make(ServerRequestInterface::class));
                $body = $psr7Response->getBody();
                $tcpConnection->send($body->getContents());
                $body?->close();
            } catch (Throwable $throwable) {
                dump($throwable);
            }
        };

        echo <<<EOT
,--.   ,--.                  ,------. ,--.  ,--.,------.  
|   `.'   | ,--,--.,--.  ,--.|  .--. '|  '--'  ||  .--. ' 
|  |'.'|  |' ,-.  | \  `'  / |  '--' ||  .--.  ||  '--' | 
|  |   |  |\ '-'  | /  /.  \ |  | --' |  |  |  ||  | --'  
`--'   `--' `--`--''--'  '--'`--'     `--'  `--'`--' 

EOT;
        Worker::runAll();
    }
}
