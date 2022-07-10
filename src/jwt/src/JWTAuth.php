<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\JWT;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Max\Config\Contracts\ConfigInterface;
use Max\JWT\Contracts\Authenticatable;
use Psr\Http\Message\ServerRequestInterface;

class JWTAuth
{
    protected string $privateKey;

    protected string $publicKey;

    protected int    $expires = 3600;

    protected string $iss;

    public function __construct(ConfigInterface $config)
    {
        $config           = $config->get('jwt');
        $this->expires    = $config['expires'];
        $this->privateKey = $config['privateKey'];
        $this->publicKey  = $config['publicKey'];
        $this->iss        = $config['iss'];
    }

    public function login(Authenticatable $user, ?int $nbf = null): string
    {
        return JWT::encode($this->createPayload($user, $nbf), $this->privateKey, 'RS256');
    }

    public function getPayload(string $token)
    {
        return JWT::decode($token, new Key($this->publicKey, 'RS256'));
    }

    public function block(string $token)
    {
    }

    public function parseToken(ServerRequestInterface $request, string $header = 'Authorization', string $query = '__token')
    {
        if ($tokenize = $request->getHeaderLine($header)) {
            $tokenize = explode(' ', $tokenize, 2);
            $token    = $tokenize[1] ?? null;
        }
        return $token ?? $request->getQueryParams()[$query] ?? null;
    }

    protected function createPayload(Authenticatable $user, ?int $nbf = null)
    {
        $now = time();
        return [
            'iss'    => $this->iss, // 签发者
            'aud'    => $user->getIdentifier(), // 接收方
            'iat'    => $now, // 签发时间
            'nbf'    => $nbf ?? $now, // 生效时间
            'exp'    => $now + $this->expires, // 有效期
            'claims' => $user->getClaims(), // 附加数据
        ];
    }
}
