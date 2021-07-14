<?php

namespace App\Http\Middleware;

use Max\Http\Request;
use function Max\app;
use function Max\response;

/**
 * Basic认证中间件
 * 如果你使用的是Apache,那么需要在配置文件中加入
 * SetEnvIf Authorization .+ HTTP_AUTHORIZATION=$0
 * 需要新建配置文件auth.php，文件内容如下
 * return [
 *     'basic' => [
 *         'user' => 'pass'
 *     ]
 * ];
 * Class BasicAuth
 * @package App\Http\Middleware
 */
class BasicAuth
{

    /**
     * 所有用户
     * @var array
     */
    protected $users = [];

    /**
     * BasicAuth constructor.
     */
    public function __construct()
    {
        $this->users = app('config')->get('auth.basic', []);
    }

    /**
     * @param Request $request
     * @param \Closure $next
     * @return \Max\Http\Response|mixed|object
     * @throws \Exception
     */
    public function handle(Request $request, \Closure $next)
    {
        if (!$request->hasHeader('Authorization')) {
            return response()->body('401')
                ->withHeader('WWW-Authenticate', 'Basic')
                ->withStatus(401);
        }
        $user = explode(':', base64_decode(substr($request->header('authorization'), 6)));
        if (isset($this->users[$user[0]]) && $this->users[$user[0]] === $user[1]) {
            return $next($request);
        }
        return response()->withHeader('WWW-Authenticate', 'Basic')->withStatus(401);
    }
}
