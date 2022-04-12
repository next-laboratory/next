<?php

namespace Max\Http\Listeners;

use Max\Di\Annotations\Inject;
use Max\Di\Annotations\MethodAnnotation;
use Max\Di\Exceptions\NotFoundException;
use Max\Event\Contracts\EventListenerInterface;
use Max\Http\Annotations\Controller;
use Max\Http\Annotations\DeleteMapping;
use Max\Http\Annotations\GetMapping;
use Max\Http\Annotations\PatchMapping;
use Max\Http\Annotations\PostMapping;
use Max\Http\Annotations\PutMapping;
use Max\Http\Annotations\RequestMapping;
use Max\Http\Contracts\MappingInterface;
use Max\Routing\Route;
use Max\Routing\RouteCollector;
use Max\Routing\Router;
use Max\WebSocket\Annotations\WebSocketHandler;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;

class RouteCollectListener implements EventListenerInterface
{
    /**
     * @var RouteCollector
     */
    #[Inject]
    protected RouteCollector $routeCollector;

    /**
     * @var Router|null
     */
    protected ?Router $router = null;

    /**
     * @return iterable
     */
    public function listen(): iterable
    {
        return [
            Controller::class,
            GetMapping::class,
            PostMapping::class,
            PutMapping::class,
            RequestMapping::class,
            DeleteMapping::class,
            PatchMapping::class,
            WebSocketHandler::class,
        ];
    }

    /**
     * @param object $event
     *
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function process(object $event): void
    {
        switch (true) {
            case $event instanceof Controller:
                $this->router = new Router([
                    'prefix'      => $event->getPrefix(),
                    'middlewares' => $event->getMiddlewares(),
                ], $this->routeCollector);
                break;
            case $event instanceof MappingInterface && $event instanceof MethodAnnotation:
                $this->routeCollector->add((new Route(
                    $event->getMethods(),
                    $this->router->getPrefix() . $event->getPath(),
                    $event->getReflectionClass()->getName() . '@' . $event->getReflectionMethod()->getName(),
                    $this->router,
                    $event->getDomain(),
                ))->middlewares($event->getMiddlewares())
                );
                break;
            case $event instanceof WebSocketHandler:
                \Max\WebSocket\RouteCollector::addRoute('/' . trim($event->getPath(), '/'), make($event->getReflectionClass()->getName()));
        }
    }
}
