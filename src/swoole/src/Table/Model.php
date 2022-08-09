<?php

namespace Max\Swoole\Table;

use ArrayAccess;
use Closure;
use InvalidArgumentException;
use JsonSerializable;
use Max\Swoole\Table\Exceptions\DuplicateKeyException;
use Max\Swoole\Table\Exceptions\ModelNotFoundException;
use Max\Utils\Collection;
use Max\Utils\Contracts\Arrayable;
use Max\Utils\Contracts\Jsonable;
use Swoole\Table;

abstract class Model implements ArrayAccess, Arrayable, JsonSerializable, Jsonable
{
    /** @var string 表名 */
    protected static string $table;
    /** @var int|string 主键 */
    protected string|int $key;
    /**
     * @var array
     */
    protected static array $fillable = [];
    /**
     * @var array
     */
    protected static array $casts = [];
    /**
     * @var array
     */
    protected array $attributes = [];

    /**
     * @param int|string $key 主键
     * @param array      $attributes
     */
    public function __construct(int|string $key, array $attributes = [])
    {
        $this->key = $key;
        if (!empty($attributes)) {
            $this->fill($attributes);
        }
    }

    /**
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->key;
    }

    /**
     * @param array $attributes
     *
     * @return static
     */
    public function fill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            $this->__set($key, $value);
        }
        return $this;
    }

    /**
     * @param string $column
     * @param int    $step
     *
     * @return int
     */
    public function increment(string $column, int $step = 1): int
    {
        $current         = (int)$this->{$column} + $step;
        $this->{$column} = $current;
        $this->save();
        return $current;
    }

    /**
     * @param string $column
     * @param int    $step
     *
     * @return int
     */
    public function decrement(string $column, int $step = -1): int
    {
        return $this->increment($column, $step);
    }

    /**
     * 获取table
     */
    protected static function getSwooleTable(): Table
    {
        return Manager::get(static::$table);
    }

    /**
     * @param string $field
     * @param        $value
     *
     * @return Collection
     */
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
     * 通过数组条件检索
     */
    public static function findByFields(array $fields): Collection
    {
        $collection = Collection::make();
        foreach (static::getSwooleTable() as $key => $item) {
            foreach ($fields as $k => $v) {
                if (!$item[$k] == $v) {
                    continue 2;
                }
            }
            $collection->push(new static($key, $item));
        }
        return $collection;
    }

    /**
     * In查询
     */
    public static function findWhereIn(string $field, array $in): Collection
    {
        return static::findWhere($field, function($k, $v, &$item) use (&$in) {
            return in_array($v, $in);
        });
    }

    /**
     * @param string $key
     *
     * @return static|null
     */
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

    /**
     * @return bool
     */
    public function save(): bool
    {
        return static::getSwooleTable()->set($this->key, $this->attributes);
    }

    /**
     * 删除
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
        return $model;
    }

    /**
     * @param string $key
     * @param array  $attributes
     *
     * @return static
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
     * @param string $key
     *
     * @return mixed
     */
    public static function exists(string $key): mixed
    {
        return static::getSwooleTable()->exists($key);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public static function destroy(string $key): bool
    {
        return static::getSwooleTable()->del($key);
    }

    /**
     * @return int
     */
    public static function count(): int
    {
        return static::getSwooleTable()->count();
    }

    /**
     * @param $name
     *
     * @return mixed|null
     */
    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * @param $name
     * @param $value
     *
     * @return void
     */
    public function __set($name, $value)
    {
        if (in_array($name, static::$fillable)) {
            if (isset(static::$casts[$name])) {
                switch (static::$casts[$name]) {
                    case 'integer':
                    case 'int':
                        $value = (int)$value;
                        break;
                }
            }
            $this->attributes[$name] = $value;
        }
    }

    /**
     * @param $offset
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->attributes[$offset]);
    }

    /**
     * @param $offset
     *
     * @return mixed|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->attributes[$offset] ?? null;
    }

    /**
     * @param $offset
     * @param $value
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        if (in_array($offset, static::$fillable)) {
            static::$fillable[$offset] = $value;
        }
    }

    /**
     * @param $offset
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset($this->attributes[$offset]);
        }
    }

    /**
     * @return Collection
     */
    public static function all(): Collection
    {
        $collection = Collection::make();
        foreach (static::getSwooleTable() as $key => $item) {
            $collection->push(new static($key, $item));
        }
        return $collection;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @param $options
     *
     * @return false|string
     */
    #[\ReturnTypeWillChange]
    public function toJson($options = 256)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }
}
