<?php

namespace Max\Engine;

use App\Context;
use Closure;

class Route
{
    public function __construct(
        public        $methods,
        public string $pattern,
        public        $handler,
        public array  $handlers = [],
    )
    {
    }

    public function handler(): Closure
    {
        return function (Context $context) {
            $context->withHandlers(...[...$this->handlers, $this->handler])->next();
        };
    }

    public function handlers(callable ...$handlers): void
    {
        $this->handlers = [...$this->handlers, ...$handlers];
    }
}