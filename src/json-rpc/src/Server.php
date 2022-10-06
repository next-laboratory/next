<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\JsonRpc;

use App\Http\Response;
use Exception;
use InvalidArgumentException;
use Max\JsonRpc\Message\Error;
use Max\JsonRpc\Message\Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class Server
{
    public static function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $rpcRequest = Request::createFromPsrRequest($request);
            if (is_null($service = RpcServiceCollector::getService($rpcRequest->getMethod()))) {
                throw new InvalidArgumentException('Method Not found', -32601);
            }
            try {
                $result = call($service, $rpcRequest->getParams());
                if ($rpcRequest->hasId()) {
                    return Response::JSON([
                        'jsonrpc' => $rpcRequest->getJsonrpc(),
                        'result'  => $result,
                        'id'      => $rpcRequest->getId(),
                    ]);
                }
                return new \Max\Http\Message\Response();
            } catch (Exception $e) {
                throw new Exception('Internal error', -32603, $e);
            }
        } catch (Throwable $e) {
            return Response::JSON(
                new \Max\JsonRpc\Message\Response(
                    null,
                    isset($rpcRequest) ? $rpcRequest->getId() : null,
                    new Error($e->getCode(), $e->getMessage(), $e->getTrace())
                )
            );
        }
    }
}
