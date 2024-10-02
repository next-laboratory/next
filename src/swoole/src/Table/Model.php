<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Next\Swoole\Table;

use ArrayAccess;
use Closure;
use InvalidArgumentException;
use JsonSerializable;
use Next\Swoole\Table\Exception\DuplicateKeyException;
use Next\Swoole\Table\Exception\ModelNotFoundException;
use Next\Utils\Collection;
use Next\Utils\Contract\Arrayable;
use Next\Utils\Contract\Jsonable;
use Swoole\Table;

abstract class Model implements ArrayAccess, Arrayable, JsonSerializable, Jsonable
{
    /**
     * @var string 表名
     */
    protected static string $table;

    /**
     * @var int|string 主键
     */
    protected string|int $key;

    /**
     * @var array 可以被填充的属性
     */
    protected static array $fillable = [];

    /**
     * @var array 类型转换，支持json，int, integer, bool, boolean
     */
    protected static array $casts = [];

    /**
     * @var array 属性
     */
    protected array $attributes = [];

    /**
     * @param int|string $key 主键
     */
    public function __construct(int|string $key, array $attributes = [])
    {
        $this->key = $key;
        if (!empty($attributes)) {
            $this->fill($attributes);
        }
    }

    /**
     * @param $name
     *
     * @return null|mixed
     */
    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if (in_array($name, static::$fillable)) {
            switch ($this->getCastType($name)) {
                case 'int':
                case 'integer':
                    $value = (int)$value;
                    break;
                case 'bool':
                case 'boolean':
                    $value = (bool)$value;
                    break;
                case 'json':
                    if (is_string($value)) {
                        $value = json_decode($value, true);
                    }
                    break;
            }
            $this->attributes[$name] = $value;
        }
    }

    protected function getCastType($name): ?string
    {
        return static::$casts[$name] ?? null;
    }

    public function __toString(): string
    {
        return $this->toJson();
    }

    public function getPrimaryKey(): string
    {
        return $this->key;
    }

    /**
     * @return $this
     */
    public function fill(array $attributes): static
    {
        foreach ($attributes as $name => $attribute) {
            $this->__set($name, $attribute);
        }

        return $this;
    }

    public function increment(string $column, int $step = 1): int
    {
        $current         = (int)$this->{$column} + $step;
        $this->{$column} = $current;
        $this->save();
        return $current;
    }

    public function decrement(string $column, int $step = 1): int
    {
        return $this->increment($column, -$step);
    }

    public static function findWhere(string $field, $value): Collection
    {
        if (!in_array($field, static::$fillable)) {
            throw new InvalidArgumentException('字段名不存在');
        }
        $collection = Collection::make();
        foreach (static::getSwooleTable() as $key => $item) {
            if (
                ($value instanceof Closure && $value($field, $item[$field], $item))
                || $item[$field] === $value
            ) {
                $collection->push(new static($key, $item));
            }
        }
        return $collection;
    }

    /**
     * 通过数组条件检索.
     */
    public static function findByFields(array $fields): Collection
    {
        $collection = Collection::make();
        foreach (static::getSwooleTable() as $key => $item) {
            foreach ($fields as $k => $v) {
                if ($item[$k] != $v) {
                    continue 2;
                }
            }
            $collection->push(new static($key, $item));
        }
        return $collection;
    }

    /**
     * In查询.
     */
    public static function findWhereIn(string $field, array $in): Collection
    {
        return static::findWhere($field, function ($k, $v, &$item) use (&$in) {
            return in_array($v, $in);
        });
    }

    public static function find(string $key): ?static
    {
        return static::getSwooleTable()->exists($key)
            ? new static($key, static::getSwooleTable()->get($key))
            : null;
    }

    /**
     * @throws ModelNotFoundException
     */
    public static function findOrFail(string $key): static
    {
        return static::find($key) ?: throw new ModelNotFoundException('数据不存在');
    }

    public function save(): bool
    {
        $attributes = $this->attributes;
        foreach ($attributes as $name => &$value) {
            if ($castType = $this->getCastType($name)) {
                if ($castType == 'json') {
                    $value = json_encode($value);
                }
            }
        }
        return static::getSwooleTable()->set($this->key, $attributes);
    }

    /**
     * 删除.
     */
    public function delete()
    {
        static::destroy($this->getPrimaryKey());
    }

    /**
     * @return static
     * @throws DuplicateKeyException
     */
    public static function create(string $key, array $attributes = []): Model
    {
        if (static::exists($key)) {
            throw new DuplicateKeyException('Duplicate key: ' . $key);
        }
        $model = new static($key, $attributes);
        $model->save();

        return static::find($key);
    }

    /**
     * @throws ModelNotFoundException
     */
    public static function update(string $key, array $attributes = []): static
    {
        $model = static::findOrFail($key);
        $model->fill($attributes);
        $model->save();
        return $model;
    }

    /**
     * @return $this
     */
    public function replace(array $attributes): static
    {
        $this->fill($attributes)->save();
        return $this;
    }

    public static function exists(string $key): bool
    {
        return static::getSwooleTable()->exists($key);
    }

    public static function destroy(string $key): bool
    {
        return static::getSwooleTable()->del($key);
    }

    public static function count(): int
    {
        return static::getSwooleTable()->count();
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->attributes);
    }

    public function offsetGet($offset): mixed
    {
        return $this->__get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->__set($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        if ($this->offsetExists($offset)) {
            unset($this->attributes[$offset]);
        }
    }

    public static function all(): Collection
    {
        $collection = Collection::make();
        foreach (static::getSwooleTable() as $key => $item) {
            $collection->push(new static($key, $item));
        }
        return $collection;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function toJson(int $options = 256): string
    {
        return (string)json_encode($this->toArray(), $options);
    }

    /**
     * 获取table.
     */
    protected static function getSwooleTable(): Table
    {
        return Manager::get(static::$table);
    }
}
