<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Http\Server\Middleware;

use Max\Config\Contract\ConfigInterface;
use Max\Http\Message\Contract\HeaderInterface;
use Max\Http\Message\Cookie;
use Max\Session\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SessionHandlerInterface;

class SessionMiddleware implements MiddlewareInterface
{
    /**
     * Cookie 过期时间【+9小时，实际1小时后过期，和时区有关】.
     */
    protected int $expires = 9 * 3600;

    protected string $name = 'MAXPHP_SESSION_ID';

    protected bool $httponly = true;

    protected string $path = '/';

    protected string $domain = '';

    protected bool $secure = true;

    /**
     * @var mixed|SessionHandlerInterface
     */
    protected SessionHandlerInterface $handler;

    public function __construct(ConfigInterface $config)
    {
        $config        = $config->get('session');
        $handler       = $config['handler'];
        $options       = $config['options'];
        $this->handler = new $handler($options);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = new Session($this->handler);
        $session->start($request->getCookieParams()[strtoupper($this->name)] ?? null);
        $request  = $request->withAttribute('Max\Session\Session', $session);
        $response = $handler->handle($request);
        $session->save();
        $session->close();

        return $this->addCookieToResponse($response, $this->name, $session->getId());
    }

    /**
     * 将cookie添加到响应
     */
    protected function addCookieToResponse(ResponseInterface $response, string $name, string $value): ResponseInterface
    {
        $cookie = new Cookie($name, $value, time() + $this->expires, $this->path, $this->domain, $this->secure, $this->httponly);

        return $response->withAddedHeader(HeaderInterface::HEADER_SET_COOKIE, $cookie->__toString());
    }
}
