<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\JsonRpc;

use BadMethodCallException;
use InvalidArgumentException;
use Max\Di\Reflection;
use Max\Http\Message\Contract\HeaderInterface;
use Max\Http\Message\Response as PsrResponse;
use Max\Http\Message\Stream\StandardStream;
use Max\JsonRpc\Message\Error;
use Max\JsonRpc\Message\Request;
use Max\JsonRpc\Message\Response as RpcResponse;
use Max\Utils\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;
use ReflectionMethod;
use Throwable;

class Server
{
    protected array $services = [];

    public function serveHttp(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $rpcRequest = Request::createFromPsrRequest($request);
            if (is_null($service = $this->getService($rpcRequest->getMethod()))) {
                throw new BadMethodCallException('Method Not found', -32601);
            }
            $result      = call($service, $rpcRequest->getParams());
            $psrResponse = new PsrResponse();
            if ($rpcRequest->hasId()) {
                $content     = json_encode([
                    'jsonrpc' => $rpcRequest->getJsonrpc(),
                    'id'      => $rpcRequest->getId(),
                    'result'  => $result,
                ]);
                $psrResponse = $psrResponse->withHeader(HeaderInterface::HEADER_CONTENT_TYPE, 'application/json; charset=utf-8')
                                           ->withBody(StandardStream::create($content));
            }

            return $psrResponse;
        } catch (Throwable $e) {
            $psrResponse = new PsrResponse();
            if (isset($rpcRequest) && $rpcRequest->hasId()) {
                $rpcResponse = new RpcResponse(
                    null,
                    $rpcRequest->getId(),
                    new Error($e->getCode(), $e->getMessage(), [
                        'file'  => $e->getFile(),
                        'line'  => $e->getLine(),
                        'trace' => $e->getTrace(),
                    ])
                );
                $psrResponse = $psrResponse->withHeader(HeaderInterface::HEADER_CONTENT_TYPE, 'application/json; charset=utf-8')
                                           ->withBody(StandardStream::create(json_encode($rpcResponse, JSON_UNESCAPED_UNICODE)));;
            }
            return $psrResponse;
        }
    }

    /**
     * @throws ReflectionException
     */
    public function register(string $name, string $class): void
    {
        if (isset($this->services[$name])) {
            throw new InvalidArgumentException('Service \'' . $name . '\' has been registered');
        }
        foreach (Reflection::methods($class, ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
            $reflectionMethodName                         = $reflectionMethod->getName();
            $this->services[$name][$reflectionMethodName] = [$class, $reflectionMethodName];
        }
    }

    protected function getService(string $name)
    {
        return Arr::get($this->services, $name);
    }
}
