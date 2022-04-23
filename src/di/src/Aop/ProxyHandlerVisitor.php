<?php

declare(strict_types=1);

/**
 * This file is part of the Max package.
 *
 * (c) Cheng Yao <987861463@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Max\Di\Aop;

use Max\Di\Annotation\Collector\AspectCollector;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\MagicConst\Function_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\NodeVisitorAbstract;

class ProxyHandlerVisitor extends NodeVisitorAbstract
{
    /**
     * @param Metadata $metadata
     */
    public function __construct(protected Metadata $metadata)
    {
    }

    /**
     * @param Node $node
     *
     * @return void|null
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Class_) {
            $node->stmts = array_merge(
                [new TraitUse([new Name('\Max\Di\Aop\ProxyHandler'),]),],
                $node->stmts
            );
        }
        if ($node instanceof ClassMethod) {
            if(AspectCollector::getMethodAspects($this->metadata->className, $node->name->toString())) {
                $methodCall = new MethodCall(
                    new Variable(new Name('this')),
                    '__callViaProxy',
                    [
                        new Arg(new Function_()),
                        new Arg(new Closure([
                            'params' => $node->getParams(),
                            'stmts'  => $node->stmts,
                        ])),
                        new Arg(new FuncCall(new Name('func_get_args')))
                    ]
                );
                $returnType = $node->getReturnType();
                if ($returnType instanceof Identifier && $returnType->name === 'void') {
                    $node->stmts = [new Expression($methodCall)];
                } else {
                    $node->stmts = [new Return_($methodCall)];
                }
            }
        }
    }
}
