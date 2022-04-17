<?php

namespace Max\Di\Aop\NodeVisitor;

use Max\Di\AnnotationManager;
use Max\Di\Contracts\AspectInterface;
use PhpParser\Node;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitorAbstract;

class ProxyHandlerVisitor extends NodeVisitorAbstract
{
    public function __construct(protected Metadata $metadata)
    {
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            $node->stmts = array_merge(
                [new Node\Stmt\TraitUse([new Node\Name('\Max\Di\Aop\Traits\ProxyHandler'),]),],
                $node->stmts
            );
        }
        if ($node instanceof Node\Stmt\ClassMethod) {
            if (array_filter(
                AnnotationManager::getMethodAnnotations($this->metadata->getClassName(), $node->name->toString()),
                fn($attribute) => $attribute instanceof AspectInterface
            )) {
                $methodCall = new Node\Expr\MethodCall(
                    new Node\Expr\Variable(new Node\Name('this')),
                    '__callViaProxy',
                    [
                        new Node\Arg(new Node\Scalar\MagicConst\Function_()),
                        new Node\Arg(new Node\Expr\Closure([
                            'params' => $node->getParams(),
                            'stmts'  => $node->stmts,
                        ])),
                        new Node\Arg(new Node\Expr\FuncCall(new Node\Name('func_get_args')))
                    ]
                );
                $returnType = $node->getReturnType();
                if ($returnType instanceof Node\Identifier && $returnType->name === 'void') {
                    $node->stmts = [new Expression($methodCall)];
                } else {
                    $node->stmts = [new Node\Stmt\Return_($methodCall)];
                }
            }
        }
    }
}
