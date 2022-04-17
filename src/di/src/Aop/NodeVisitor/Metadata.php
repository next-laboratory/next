<?php

namespace Max\Di\Aop\NodeVisitor;

class Metadata
{
    public function __construct(protected string $className)
    {
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }
}
