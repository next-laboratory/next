<?php

namespace Max\JsonRpc;

use App\Http\Response;
use Exception;
use InvalidArgumentException;
use Max\Http\Message\Contract\HeaderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class JsonRpcServer
{
    public static function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            if (!str_contains($request->getHeaderLine(HeaderInterface::HEADER_CONTENT_TYPE), 'application/json')) {
                throw new Exception('Invalid Request', -32600);
            }
            $body   = $request->getBody()->getContents();
            $parsed = json_decode($body, true);
            if (!isset($parsed['jsonrpc'], $parsed['method'])) {
                throw new InvalidArgumentException('Parse error', -32700);
            }
            if (is_null($service = RpcServiceCollector::getService($parsed['method']))) {
                throw new InvalidArgumentException('Method Not found', -32601);
            }
            try {
                $result = call($service, $parsed['params'] ?? []);
                if (isset($parsed['id'])) {
                    return Response::JSON([
                        'jsonrpc' => '2.0',
                        'result'  => $result,
                        'id'      => $parsed['id'],
                    ]);
                }
                return new \Max\Http\Message\Response();
            } catch (Exception $e) {
                throw new Exception('Internal error', -32603, $e);
            }
        } catch (Throwable $e) {
            return Response::JSON([
                'jsonrpc' => '2.0',
                'id'      => $parsed['id'] ?? null,
                'error'   => [
                    'code'    => $e->getCode(),
                    'message' => $e->getMessage(),
                    'data'    => $e->getTrace(),
                ]
            ]);
        }
    }
}
