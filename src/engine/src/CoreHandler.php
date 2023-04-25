<?php

namespace Max\Engine;

use App\Context;
use App\Exception\HttpException;
use Closure;
use Max\VarDumper\Dumper;
use Max\VarDumper\DumperHandler;
use Throwable;

class CoreHandler
{
    use DumperHandler;

    public static function errorHandle(Context $context): void
    {
        try {
            $context->next();
        } catch (Throwable $e) {
            if ($e instanceof Dumper) {
                $context->HTML(200, self::convertToHtml($e));
            }
            $context->string($e instanceof HttpException ? $e->getStatusCode() : 500, $e->getMessage());
        }
    }

    public static function routeResolver(Engine $engine): Closure
    {
        return function (Context $context) use ($engine) {
            $uri = $context->request->path();
            $httpMethod = $context->request->method();
            $routeInfo = $engine->resolve($httpMethod, $uri);
            $context->setValues($routeInfo[2]);
            $context->withHandlers($routeInfo[1])->next();
        };

    }
}