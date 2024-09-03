<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Aop;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Parser;
use PhpParser\ParserFactory;

class AstManager
{
    protected Parser $parser;

    protected array $container = [];

    public function __construct()
    {
        $this->parser = (new ParserFactory())->createForHostVersion();
    }

    public function getNodes(string $realpath)
    {
        return $this->parser->parse(file_get_contents($realpath));
    }

    public function getClassesByRealPath(string $realpath): array
    {
        $classes = [];
        foreach ($this->getNodes($realpath) as $stmt) {
            if ($stmt instanceof Namespace_) {
                $namespace = $stmt->name->toCodeString();
                foreach ($stmt->stmts as $subStmt) {
                    // TODO 不支持Trait
                    if ($subStmt instanceof Class_) {
                        $classes[] = $namespace . '\\' . $subStmt->name->toString();
                    }
                }
            }
        }
        return $classes;
    }
}
