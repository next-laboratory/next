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

namespace Max\Swoole;

class ConfigProvider
{
    /**
     * @return array
     */
    public function __invoke(): array
    {
        return [
            'bindings' => [
                'Psr\Http\Message\ServerRequestInterface' => 'Max\Swoole\Http\ServerRequest',
                'Psr\Http\Message\ResponseInterface'      => 'Max\Swoole\Http\Response',
                'Psr\Http\Server\RequestHandlerInterface' => 'Max\Swoole\Http\RequestHandler',
            ]
        ];
    }

    /**
     * publish
     */
    public function publish()
    {
        $to = dirname(__DIR__, 4) . '/config/swoole.php';
        file_exists($to) || copy(__DIR__ . '/../publish/swoole.php', $to);
    }
}
