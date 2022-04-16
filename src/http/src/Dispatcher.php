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

namespace Max\Http;

use Max\Di\Exceptions\NotFoundException;
use Max\Http\Exceptions\InvalidResponseBodyException;
use Max\Routing\Route;
use Max\Utils\Contracts\Arrayable;
use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionException;
use Stringable;

class Dispatcher implements RequestHandlerInterface
{
    /**
     * @param ResponseInterface $response
     */
    public function __construct(protected ResponseInterface $response)
    {
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     * @throws ReflectionException|ContainerExceptionInterface|InvalidResponseBodyException|NotFoundException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var Route $route */
        $route  = $request->route();
        $action = $route->getAction();
        $params = $route->getParameters();
        if (is_string($action)) {
            $action = explode('@', $action, 2);
        }
        if (is_array($action)) {
            [$controller, $action] = $action;
            $action = [make($controller), $action];
        }

        return $this->autoResponse(call($action, array_filter($params, fn($value) => !is_null($value))));
    }

    /**
     * @param $response
     *
     * @return ResponseInterface
     * @throws InvalidResponseBodyException
     */
    protected function autoResponse($response): ResponseInterface
    {
        if ($response instanceof ResponseInterface) {
            return $response;
        }

        $response = match (true) {
            $response instanceof Arrayable => $response->toArray(),
            $response instanceof Stringable => $response->__toString(),
            default => $response,
        };

        // 标量或null按照html输出，其他按照json输出，错误会被捕获
        $response = match (true) {
            is_scalar($response) || is_null($response) => $this->response->html((string)$response),
            default => $this->response->json($response),
        };
        $this->response->setPsr7($response);

        return $this->response;
    }
}
