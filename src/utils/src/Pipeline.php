<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Utils;

use Closure;
use Psr\Container\ContainerInterface;

/**
 * Most of the methods in this file come from illuminate
 * thanks Laravel Team provide such a useful class.
 */
class Pipeline
{
    protected array $pipes = [];

    protected mixed $passable;

    protected string $method = 'handle';

    public function __construct(
        protected ?ContainerInterface $container = null
    ) {
    }

    public function send(mixed $passable): static
    {
        $this->passable = $passable;

        return $this;
    }

    /**
     * Set the method to call on the pipes.
     */
    public function via(string $method): static
    {
        $this->method = $method;

        return $this;
    }

    public function through(array $pipes): static
    {
        $this->pipes = $pipes;

        return $this;
    }

    public function then(Closure $destination): mixed
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes),
            $this->carry(),
            $this->prepareDestination($destination)
        );
        return $pipeline($this->passable);
    }

    protected function prepareDestination(Closure $destination): Closure
    {
        return static function ($passable) use ($destination) {
            return $destination($passable);
        };
    }

    protected function carry(): Closure
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                if (is_callable($pipe)) {
                    // If the pipe is an instance of a Closure, we will just call it directly but
                    // otherwise we'll resolve the pipes out of the container and call it with
                    // the appropriate method and arguments, returning the results back out.
                    return $pipe($passable, $stack);
                }
                if (! is_object($pipe)) {
                    [$name, $parameters] = $this->parsePipeString($pipe);

                    // If the pipe is a string we will parse the string and resolve the class out
                    // of the dependency injection container. We can then build a callable and
                    // execute the pipe function giving in the parameters that are required.
                    $pipe = isset($this->container) ? $this->container->make($name) : new $name();

                    $parameters = array_merge([$passable, $stack], $parameters);
                } else {
                    // If the pipe is already an object we'll just make a callable and pass it to
                    // the pipe as-is. There is no need to do any extra parsing and formatting
                    // since the object we're given was already a fully instantiated object.
                    $parameters = [$passable, $stack];
                }

                $carry = method_exists($pipe, $this->method) ? $pipe->{$this->method}(...$parameters) : $pipe(...$parameters);

                return $this->handleCarry($carry);
            };
        };
    }

    /**
     * Parse full pipe string to get name and parameters.
     */
    protected function parsePipeString(string $pipe): array
    {
        [$name, $parameters] = array_pad(explode(':', $pipe, 2), 2, []);

        if (is_string($parameters)) {
            $parameters = explode(',', $parameters);
        }

        return [$name, $parameters];
    }

    /**
     * Handle the value returned from each pipe before passing it to the next.
     */
    protected function handleCarry(mixed $carry): mixed
    {
        return $carry;
    }
}
