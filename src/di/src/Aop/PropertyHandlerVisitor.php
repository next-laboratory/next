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

use Max\Di\ReflectionManager;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use ReflectionNamedType;
use ReflectionUnionType;

class PropertyHandlerVisitor extends NodeVisitorAbstract
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
    public function enterNode(Node $node)
    {
        if ($node instanceof ClassMethod && $node->name->toString() === '__construct') {
            $this->metadata->hasConstructor = true;
        }
    }

    /**
     * @param Node $node
     *
     * @return void|null
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Class_) {
            $params          = [];
            $reflectionClass = ReflectionManager::reflectClass($this->metadata->className);
            if ($reflectionConstructor = $reflectionClass->getConstructor()) {
                $declaringClass = $reflectionConstructor->getDeclaringClass()->getName();
                if ($classPath = $this->metadata->loader->findFile($declaringClass)) {
                    $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
                    $ast    = $parser->parse(file_get_contents($classPath));
                    foreach ($ast as $stmt) {
                        if ($stmt instanceof Node\Stmt\Namespace_) {
                            foreach ($stmt->stmts as $subStmt) {
                                if ($subStmt instanceof Class_) {
                                    foreach ($subStmt->stmts as $internal) {
                                        if ($internal instanceof ClassMethod && $internal->name->toString() === '__construct') {
                                            $params = $internal->getParams();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                foreach ($reflectionConstructor->getParameters() as $key => $reflectionParameter) {
                    $type = $reflectionParameter->getType();
                    if (is_null($type)
                        || ($type instanceof ReflectionNamedType && $type->isBuiltin())
                        || $type instanceof ReflectionUnionType
                        || ($type->getName()) === 'Closure') {
                        continue;
                    } else {
                        $params[$key]->type = new Name('\\' . $type->getName());
                    }
                }
            }
            $c = [];
            if (!$this->metadata->hasConstructor) {
                $constructor        = new ClassMethod('__construct', [
                    'params' => $params,
                ]);
                $constructor->flags = 1;
                if ($node->extends) {
                    $constructor->stmts[] = new If_(new FuncCall(new Name('method_exists'), [
                        new ClassConstFetch(new Name('parent'), 'class'),
                        new String_('__construct'),
                    ]), [
                        'stmts' => [
                            new Expression(new StaticCall(
                                new ConstFetch(new Name('parent')), '__construct',
                                [new Arg(new FuncCall(new Name('func_get_args')), unpack: true),]
                            ))
                        ]
                    ]);
                }
                $constructor->stmts[] = new Expression(new MethodCall(
                    new Variable(new Name('this')), '__handleProperties'
                ));
                $c                    = [$constructor];
            }

            $node->stmts = array_merge([new TraitUse([new Name('\Max\Di\Aop\PropertyHandler'),])], $c, $node->stmts);
        }
        if ($node instanceof ClassMethod && $node->name->toString() === '__construct') {
            array_unshift($node->stmts,
                new Expression(new MethodCall(new Variable(new Name('this')), '__handleProperties'))
            );
        }
    }
}
