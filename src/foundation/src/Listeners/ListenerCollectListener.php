<?php

namespace Max\Foundation\Listeners;

use Max\Di\Annotations\ClassAnnotation;
use Max\Di\Annotations\Inject;
use Max\Di\Exceptions\NotFoundException;
use Max\Event\Contracts\EventListenerInterface;
use Max\Event\ListenerProvider;
use Max\Foundation\Annotations\Listen;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use ReflectionException;

class ListenerCollectListener implements EventListenerInterface
{
    /**
     * @var ListenerProvider
     */
    #[Inject]
    protected ListenerProvider $listenerProvider;

    /**
     * @var ContainerInterface
     */
    #[Inject]
    protected ContainerInterface $container;

    /**
     * @return iterable
     */
    public function listen(): iterable
    {
        return [
            Listen::class,
        ];
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     * @throws NotFoundException
     */
    public function process(object $event): void
    {
        /** @var ClassAnnotation $event */
        $this->listenerProvider->addListener($this->container->make($event->getReflectionClass()->getName()));
    }
}
