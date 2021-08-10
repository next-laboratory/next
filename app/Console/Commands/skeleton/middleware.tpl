<?php
declare(strict_types=1);

namespace {{namespace}};

class {{class}}
{

    public function handle($request, \Closure $next)
    {
        return $next($request);
    }

}
