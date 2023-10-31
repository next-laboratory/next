<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Session;

use Next\Utils\Arr;
use Next\Utils\Contract\PackerInterface;

class Session
{
    /**
     * Session not started.
     */
    protected const STATE_NOT_STARTED = 0;

    /**
     * Session started.
     */
    protected const STATE_STARTED = 1;

    /**
     * Session destroyed.
     */
    protected const STATE_DESTROYED = 2;

    /**
     * Session ID.
     */
    protected string $id = '';

    /**
     * Session data.
     */
    protected array $data = [];

    /**
     * Session state.
     */
    protected int $state = 0;

    public function __construct(
        protected \SessionHandlerInterface $sessionHandler,
        protected PackerInterface $packer,
    ) {
    }

    /**
     * Start a new session.
     */
    public function start(string $id = ''): void
    {
        if ($this->isStarted()) {
            throw new \BadMethodCallException('the session is started');
        }
        $this->sessionHandler->open('', '');
        $this->id = ($id && $this->isValidId($id)) ? $id : \session_create_id();
        if ($data = $this->sessionHandler->read($this->id)) {
            $this->data = (array) ($this->packer->unpack($data) ?: []);
        }
        $this->state = static::STATE_STARTED;
    }

    /**
     * Save session data.
     */
    public function save(): void
    {
        $this->sessionHandler->write($this->id, $this->packer->pack($this->data));
    }

    /**
     * Generate a new session id.
     */
    public function regenerateId(): void
    {
        $this->id = \session_create_id();
    }

    /**
     * Close the session.
     */
    public function close(): bool
    {
        return $this->sessionHandler->close();
    }

    /**
     * Check whether the key exists.
     */
    public function has(string $key): bool
    {
        return Arr::has($this->data, $key);
    }

    /**
     * Get the value of $key, or $default if it does not exist.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->data, $key, $default);
    }

    /**
     * Set value of the key.
     */
    public function set(string $key, mixed $value): void
    {
        Arr::set($this->data, $key, $value);
    }

    /**
     * Gets and deletes, or returns default if it does not exist.
     */
    public function pull(string $key, mixed $default = null): mixed
    {
        $data = $this->get($key, $default);
        $this->remove($key);
        return $data;
    }

    /**
     * Delete data for $key.
     */
    public function remove(string $key): void
    {
        Arr::forget($this->data, $key);
    }

    /**
     * Return all session data.
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Destroy the session.
     */
    public function destroy(): void
    {
        $this->sessionHandler->destroy($this->id);
        $this->data  = [];
        $this->id    = '';
        $this->state = static::STATE_DESTROYED;
    }

    /**
     * Get the session id.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set the session id.
     */
    public function setId(string $id): void
    {
        if (! $this->isValidId($id)) {
            throw new \InvalidArgumentException('The length of the session ID must be 40.');
        }
        $this->id = $id;
    }

    /**
     * Check whether the session is started.
     */
    public function isStarted(): bool
    {
        return $this->state === static::STATE_STARTED;
    }

    /**
     * Check whether the session is destroyed.
     */
    public function isDestroyed(): bool
    {
        return $this->state == static::STATE_DESTROYED;
    }

    /**
     * Check whether $id is a valid session id.
     */
    protected function isValidId(string $id): bool
    {
        return \ctype_alnum($id);
    }
}
