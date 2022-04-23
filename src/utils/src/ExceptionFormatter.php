<?php

namespace Max\Utils;

class ExceptionFormatter
{
    public function __construct(protected \Throwable $throwable)
    {
    }

    public function format()
    {
        return sprintf(
            "%s: %s in %s :%d\n%s\n",
            $this->throwable::class,
            $this->throwable->getMessage(),
            $this->$this->throwable->getFile(),
            $this->throwable->getLine(),
            $this->throwable->getTraceAsString()
        );
    }

    public function __toString(): string
    {
        return $this->format();
    }
}
