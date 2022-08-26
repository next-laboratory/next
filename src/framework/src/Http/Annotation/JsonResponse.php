<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Http\Annotation;

use Attribute;
use Closure;
use Max\Aop\Contract\AspectInterface;
use Max\Aop\JoinPoint;
use Max\Http\Message\Contract\HeaderInterface;
use Max\Http\Message\Response;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class JsonResponse implements AspectInterface
{
    public function process(JoinPoint $joinPoint, Closure $next): mixed
    {
        $data = json_encode($next($joinPoint), JSON_UNESCAPED_UNICODE);
        return new Response(200, [HeaderInterface::HEADER_CONTENT_TYPE => 'application/json'], $data);
    }
}
