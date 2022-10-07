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
use Max\Http\Message\Contract\HeaderInterface;
use Max\Http\Message\Response as HttpResponse;
use Max\Http\Message\Stream\StandardStream;
use Max\JsonRpc\Message\Error;
use Max\JsonRpc\Message\Request;
use Max\JsonRpc\Message\Response as RpcResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class Server
{
    public function serveHttp(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $rpcRequest = Request::createFromPsrRequest($request);
            if (is_null($service = ServiceCollector::getService($rpcRequest->getMethod()))) {
                throw new BadMethodCallException('Method Not found', -32601);
            }
            $result   = call($service, $rpcRequest->getParams());
            $response = new HttpResponse();
            if ($rpcRequest->hasId()) {
                $content  = json_encode([
                    'jsonrpc' => $rpcRequest->getJsonrpc(),
                    'result'  => $result,
                    'id'      => $rpcRequest->getId(),
                ]);
                $response = $response->withHeader(HeaderInterface::HEADER_CONTENT_TYPE, 'application/json; charset=utf-8')
                                     ->withBody(StandardStream::create($content));
            }

            return $response;
        } catch (Throwable $e) {
            $rpcResponse = new RpcResponse(
                null,
                isset($rpcRequest) ? $rpcRequest->getId() : null,
                new Error($e->getCode(), $e->getMessage(), [
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine(),
                    'trace' => $e->getTrace(),
                ])
            );
            return new HttpResponse(
                headers: [HeaderInterface::HEADER_CONTENT_TYPE => 'application/json; charset=utf-8'],
                body: StandardStream::create(json_encode($rpcResponse))
            );
        }
    }
}
