<?php

namespace App\Http\Middleware;

use Max\Exception\HttpException;
use Max\Http\Request;

/**
 * Basic认证中间件
 * 如果你使用的是Apache,那么需要在配置文件中加入
 * SetEnvIf Authorization .+ HTTP_AUTHORIZATION=$0
 * 需要在user属性中加入user和pass
 * Class BasicAuth
 * @package App\Http\Middleware
 */
class BasicAuthentication
{

    /**
     * 所有用户
     * @var array
     */
    protected $users = [
        'user' => 'pass'
    ];

    /**
     * @param Request $request
     * @param \Closure $next
     * @return \Max\Http\Response|mixed|object
     * @throws \Exception
     */
    public function handle(Request $request, \Closure $next)
    {
        if (empty($this->users)) {
            return response()->body(app('error')->getMessage(new \Exception('没有Basic认证相关信息！')));
        }
        if (!$request->hasHeader('Authorization')) {
            response()->withHeader('WWW-Authenticate', 'Basic');
            throw new HttpException('请先通过Basic验证！', 401);
        }
        $user = explode(':', base64_decode(substr($request->header('authorization'), 6)));
        if (isset($this->users[$user[0]]) && $this->users[$user[0]] === $user[1]) {
            return $next($request);
        }
        return response()->withHeader('WWW-Authenticate', 'Basic')->withStatus(401);
    }
}
