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

namespace Max\Swoole\Http;

use Max\Http\Message\Response as PsrResponse;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Swoole\Coroutine\Http\Client as SwooleCoroutineClient;

class Client
{
    /**
     * @var SwooleCoroutineClient
     */
    protected SwooleCoroutineClient $client;

    /**
     * @param string $host
     * @param int    $port
     * @param bool   $ssl
     * @param array  $options
     */
    public function __construct(string $host, int $port, bool $ssl = false, array $options = [])
    {
        $this->client = new SwooleCoroutineClient($host, $port, $ssl);
        $this->client->set($options);
    }

    /**
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $client = $this->client;
        $client->setMethod($request->getMethod());
        $client->setHeaders($request->getHeaders());
        $client->setData($request->getBody()->getContents());
        return new PsrResponse(
            $client->execute($request->getUri()->getPath()),
            $client->getHeaders(),
            $client->getBody(),
        );
    }
}
