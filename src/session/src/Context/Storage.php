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

namespace Max\Session\Context;

use ArrayAccess;
use Max\Utils\Arr;

class Storage
{
    /**
     * 构造
     *
     * @param string $id SessionID
     * @param array $data Session数据
     */
    public function __construct(protected string $id, protected array $data = [])
    {
    }

    /**
     * 返回SessionID
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     *设置SessionID
     *
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * 返回当前key对应的Session
     *
     * @param string $key
     * @param        $default
     *
     * @return array|ArrayAccess|mixed
     */
    public function get(string $key, $default = null)
    {
        return Arr::get($this->data, $key, $default);
    }

    /**
     * 设置Session
     *
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
     * 删除
     *
     * @param string $key
     *
     * @return void
     */
    public function remove(string $key)
    {
        Arr::forget($this->data, $key);
    }

    /**
     * 判断是否存在
     *
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * 返回全部
     *
     * @return array
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function pull(string $key, mixed $default = null): mixed
    {
        return Arr::pull($this->data, $key, $default);
    }
}
