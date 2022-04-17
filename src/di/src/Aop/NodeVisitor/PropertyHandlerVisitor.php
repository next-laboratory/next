<?php

namespace Max\Di\Aop\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class PropertyHandlerVisitor extends NodeVisitorAbstract
{
    public function __construct(protected Metadata $metadata)
    {
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            $constructor        = new Node\Stmt\ClassMethod('__construct');
            $constructor->flags = 1;
            if ($node->extends) {
                $constructor->stmts[] = new Node\Stmt\If_(new Node\Expr\FuncCall(new Node\Name('method_exists'), [
                    new Node\Expr\ClassConstFetch(new Node\Name('parent'), 'class'),
                    new Node\Scalar\String_('__construct'),
                ]), [
                    'stmts' => [
                        new Node\Stmt\Expression(new Node\Expr\StaticCall(
                            new Node\Expr\ConstFetch(new Node\Name('parent')), '__construct',
                            [new Node\Arg(new Node\Expr\FuncCall(new Node\Name('func_get_args')), unpack: true),]
                        ))
                    ]
                ]);
            }

            $constructor->stmts[] = new Node\Stmt\Expression(new Node\Expr\MethodCall(
                new Node\Expr\Variable(new Node\Name('this')), '__handleProperties'
            ));
            $node->stmts          = array_merge([new Node\Stmt\TraitUse([new Node\Name('\Max\Di\Aop\Traits\PropertyHandler'),])], [$constructor], $node->stmts);
        }
    }
}
