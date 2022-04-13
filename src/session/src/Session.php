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

use Exception;
use Max\Session\Context\Storage;
use Max\Utils\Context;
use SessionHandlerInterface;
use Throwable;
use function is_string;
use function md5;
use function microtime;
use function serialize;
use function session_create_id;
use function unserialize;


class Session
{
    /**
     * Session句柄
     *
     * @var SessionHandlerInterface
     */
    protected SessionHandlerInterface $handler;

    /**
     * @param array $config
     *
     * @throws Exception
     */
    public function __construct(array $config)
    {
        try {
            $config = $config['stores'][$config['default']];
            $handler = $config['handler'];
            $this->handler = new $handler($config['options']);
        } catch (Throwable $throwable) {
            throw new Exception('The configuration file may be incorrect: ' . $throwable->getMessage());
        }
    }

    /**
     * @param string $id
     *
     * @return void
     */
    public function start(string $id): void
    {
        $data = $this->handler->read($id);
        if (is_string($data)) {
            $data = unserialize($data) ?: [];
        }

        Context::put(Storage::class, new Storage($id, $data));
    }

    /**
     * @return void
     */
    public function save(): void
    {
        $data = Context::get(Storage::class)?->all() ?: [];
        $this->handler->write($this->getId(), serialize($data));
        $this->handler->close();
    }

    /**
     * @return SessionHandlerInterface
     */
    public function getHandler(): SessionHandlerInterface
    {
        return $this->handler;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return (bool)(Context::get(Storage::class)?->has($key));
    }

    /**
     * @param string $key
     * @param null $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null): mixed
    {
        return Context::get(Storage::class)?->get($key, $default);
    }

    /**
     * @param string $key
     * @param        $value
     *
     * @return array
     */
    public function set(string $key, $value): array
    {
        return Context::get(Storage::class)?->set($key, $value);
    }

    /**
     * @param string $key
     * @param null $default
     *
     * @return mixed
     */
    public function pull(string $key, mixed $default = null): mixed
    {
        return Context::get(Storage::class)?->pull($key, $default);
    }

    /**
     * @param string $key
     *
     * @return void
     */
    public function remove(string $key): void
    {
        Context::get(Storage::class)?->remove($key);
    }

    /**
     * @return void
     */
    public function destroy(): void
    {
        $storage = Context::get(Storage::class);
        $this->handler->destroy($storage->getId());
        Context::delete(Storage::class);
    }

    /**
     * @param SessionHandlerInterface $handler
     *
     * @return void
     */
    public function setHandler(SessionHandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return Context::get(Storage::class)?->getId() ?? $this->refreshId();
    }

    /**
     * @return string
     */
    public function refreshId(): string
    {
        return md5(microtime(true) . session_create_id());
    }

    /**
     * @param $maxLifeTime
     */
    public function gc($maxLifeTime)
    {
        $this->getHandler()->gc($maxLifeTime);
    }

    /**
     * @param string $id
     *
     * @return void
     */
    public function setId(string $id)
    {
        Context::get(Storage::class)?->setId($id);
    }
}