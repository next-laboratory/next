<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Next\JsonRpc;

use BadMethodCallException;
use InvalidArgumentException;
use Next\Di\Reflection;
use Next\Http\Message\Response as PsrResponse;
use Next\Http\Message\Stream\StandardStream;
use Next\JsonRpc\Message\Error;
use Next\JsonRpc\Message\Request;
use Next\JsonRpc\Message\Response as RpcResponse;
use Next\Utils\Arr;
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
                $psrResponse = $psrResponse->withHeader('Content-Type', 'application/json; charset=utf-8')
                                           ->withBody(StandardStream::create($content));
            }

            return $psrResponse;
        } catch (Throwable $e) {
            $psrResponse = new PsrResponse();
            if (!isset($rpcRequest) || ($rpcRequest->hasId())) {
                $rpcResponse = new RpcResponse(
                    null,
                    isset($rpcRequest) ? $rpcRequest->getId() : null,
                    new Error($e->getCode(), $e->getMessage(), [
                        'file'  => $e->getFile(),
                        'line'  => $e->getLine(),
                        'trace' => $e->getTrace(),
                    ])
                );
                $psrResponse = $psrResponse->withHeader('Content-Type', 'application/json; charset=utf-8')
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
