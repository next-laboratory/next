<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\JsonRpc;

use Exception;
use GuzzleHttp\Client as GzClient;
use GuzzleHttp\Exception\GuzzleException;
use Max\JsonRpc\Message\Request;
use Max\JsonRpc\Message\Response;
use Psr\Http\Message\ResponseInterface;

class Client
{
    protected GzClient $client;

    public function __construct(
        protected string $uri = '',
    ) {
        $this->client = new GzClient(['base_uri' => $this->uri]);
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function call(Request $request, string $requestMethod = 'GET')
    {
        if (!$request->hasId()) {
            $request->setId(md5(uniqid()));
        }
        return Response::createFromPsrResponse($this->sendRequest($request, $requestMethod));
    }

    /**
     * @throws GuzzleException
     */
    public function sendRequest(Request $request, string $requestMethod = 'GET'): ResponseInterface
    {
        return $this->client->request($requestMethod, '/', ['json' => $request]);
    }

    /**
     * @throws GuzzleException
     */
    public function notify(Request $request, string $requestMethod = 'GET'): void
    {
        $this->sendRequest($request, $requestMethod);
    }
}
