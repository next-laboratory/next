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

use Max\Http\Message\Bags\CookieBag;
use Max\Http\Message\Stream\StringStream;
use Max\Utils\Context;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class Response implements ResponseInterface
{
    use Message;

    /**
     * @param $data
     *
     * @return ResponseInterface
     */
    public function json($data): ResponseInterface
    {
        return $this->withHeader('Content-Type', 'application/json; charset=utf-8')
                    ->withBody(new StringStream(json_encode($data)));
    }

    /**
     * TODO
     *
     * @param Cookie $cookie
     */
    public function withCookie(Cookie $cookie)
    {
        if (!($bag = Context::get($key = CookieBag::class))) {
            $bag = new $key();
        }
        $bag->add($cookie);
        Context::put($key, $bag);

        return $this;
    }

    /**
     * TODO
     *
     * @return array
     */
    public function getCookies(): array
    {
        if ($bag = Context::get(CookieBag::class)) {
            return $bag->all();
        }
        return [];
    }

    /**
     * @param string $html
     *
     * @return ResponseInterface
     */
    public function html(string $html): ResponseInterface
    {
        return $this->withHeader('Content-Type', 'text/html; charset=utf-8')
                    ->withBody(new StringStream($html));
    }

    /**
     * @inheritDoc
     */
    public function getStatusCode()
    {
        return $this->getPsr7()->getStatusCode();
    }

    /**
     * @inheritDoc
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        return $this->getPsr7()->withStatus($code, $reasonPhrase);
    }

    /**
     * @inheritDoc
     */
    public function getReasonPhrase()
    {
        return $this->getPsr7()->getReasonPhrase();
    }

    /**
     * @return mixed
     */
    protected function getPsr7()
    {
        if ($psr7Response = Context::get(ResponseInterface::class)) {
            return $psr7Response;
        }
        throw new RuntimeException('There is no response instance in the context.');
    }

    /**
     * @param ResponseInterface $response
     */
    public function setPsr7(ResponseInterface $response)
    {
        Context::put(ResponseInterface::class, $response);
    }
}
