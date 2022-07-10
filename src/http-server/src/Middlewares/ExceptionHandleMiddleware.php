<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Http\Server\Middlewares;

use InvalidArgumentException;
use Max\Http\Server\Contracts\ExceptionHandlerInterface;
use Max\Http\Server\Contracts\StoppableExceptionHandlerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionException;
use Throwable;

class ExceptionHandleMiddleware implements MiddlewareInterface
{
    /**
     * @var ExceptionHandlerInterface[]|string[]
     */
    protected array $exceptionHandlers = [];

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function __construct(ContainerInterface $container)
    {
        foreach ($this->exceptionHandlers as $key => $exceptionHandler) {
            $this->exceptionHandlers[$key] = $container->make($exceptionHandler);
        }
    }

    /**
     * @throws Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $throwable) {
            $finalResponse = null;
            foreach ($this->exceptionHandlers as $exceptionHandler) {
                if ($exceptionHandler->isValid($throwable)) {
                    if ($response = $exceptionHandler->handle($throwable, $request)) {
                        $finalResponse = $response;
                    }
                    if ($exceptionHandler instanceof StoppableExceptionHandlerInterface) {
                        return $finalResponse instanceof ResponseInterface ? $finalResponse
                            : throw new InvalidArgumentException(
                                'The final exception handler must return an instance of Psr\Http\Message\ResponseInterface'
                            );
                    }
                }
            }
            throw $throwable;
        }
    }
}
