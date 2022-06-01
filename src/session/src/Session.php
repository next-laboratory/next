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

namespace Max\Session;

use Max\Config\Contracts\ConfigInterface;
use Max\Context\Context;
use Max\Session\Context\Storage;
use Max\Session\Exceptions\SessionException;
use SessionHandlerInterface;
use function is_string;
use function md5;
use function microtime;
use function serialize;
use function session_create_id;
use function unserialize;

class Session
{
    protected SessionHandlerInterface $handler;

    public function __construct(ConfigInterface $config)
    {
        $config        = $config->get('session');
        $config        = $config['stores'][$config['default']];
        $handler       = $config['handler'];
        $this->handler = new $handler($config['options']);
    }

    public function start(?string $id = null): void
    {
        $id   ??= $this->createId();
        $data = $this->handler->read($id);
        if (!$data) {
            $data = [];
        }
        if (is_string($data)) {
            $data = unserialize($data) ?: [];
        }

        Context::put(Storage::class, new Storage($id, $data));
    }

    public function save(): void
    {
        $data = Context::get(Storage::class)?->all() ?: [];
        $this->handler->write($this->getId(), serialize($data));
    }

    public function close(): bool
    {
        return $this->handler->close();
    }

    public function has(string $key): bool
    {
        return (bool)(Context::get(Storage::class)?->has($key));
    }

    public function get(string $key, $default = null): mixed
    {
        return Context::get(Storage::class)?->get($key, $default);
    }

    public function set(string $key, $value): array
    {
        return Context::get(Storage::class)?->set($key, $value);
    }

    public function pull(string $key, mixed $default = null): mixed
    {
        return Context::get(Storage::class)?->pull($key, $default);
    }

    public function remove(string $key): void
    {
        Context::get(Storage::class)?->remove($key);
    }

    public function destroy(): void
    {
        $storage = Context::get(Storage::class);
        $this->handler->destroy($storage->getId());
        Context::delete(Storage::class);
    }

    public function getId(): string
    {
        return Context::get(Storage::class)?->getId() ?? throw new SessionException('The session is not started.');
    }

    protected function createId(): string
    {
        return md5(microtime(true) . session_create_id());
    }

    public function setId(string $id): void
    {
        Context::get(Storage::class)?->setId($id);
    }
}
