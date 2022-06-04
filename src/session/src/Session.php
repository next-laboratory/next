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

use Max\Session\Exceptions\SessionException;
use Max\Utils\Arr;
use SessionHandlerInterface;
use function is_string;
use function md5;
use function microtime;
use function serialize;
use function session_create_id;
use function unserialize;

class Session
{
    /**
     * Session ID.
     *
     * @var string
     */
    protected string $id = '';

    /**
     * Session data.
     *
     * @var array
     */
    protected array $data = [];

    public function __construct(protected SessionHandlerInterface $sessionHandler)
    {
    }

    /**
     * Start a new session.
     *
     * @param string|null $id
     *
     * @return void
     */
    public function start(?string $id = null): void
    {
        $this->id = $id ?? $this->createId();
        $data     = $this->sessionHandler->read($this->id);
        if (!$data) {
            $data = [];
        }
        if (is_string($data)) {
            $data = unserialize($data) ?: [];
        }
        $this->data = $data;
    }

    /**
     * @return void
     */
    public function save(): void
    {
        $this->sessionHandler->write($this->id, serialize($this->data));
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        return $this->sessionHandler->close();
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return Arr::has($this->data, $key);
    }

    /**
     * @param string $key
     * @param        $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null): mixed
    {
        return Arr::get($this->data, $key, $default);
    }

    /**
     * @param string $key
     * @param        $value
     *
     * @return array
     */
    public function set(string $key, $value): array
    {
        return Arr::set($this->data, $key, $value);
    }

    /**
     * @param string     $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function pull(string $key, mixed $default = null): mixed
    {
        $data = $this->get($key, $default);
        $this->remove($key);
        return $data;
    }

    /**
     * @param string $key
     *
     * @return void
     */
    public function remove(string $key): void
    {
        Arr::forget($this->data, $key);
    }

    /**
     * @return void
     */
    public function destroy(): void
    {
        $this->sessionHandler->destroy($this->id);
        $this->data = [];
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id ?: throw new SessionException('The session is not started.');
    }

    /**
     * @return string
     */
    protected function createId(): string
    {
        return md5(microtime(true) . session_create_id());
    }

    /**
     * @param string $id
     *
     * @return void
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }
}
