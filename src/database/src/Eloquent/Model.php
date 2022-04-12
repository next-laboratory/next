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

namespace Max\Database\Eloquent;

use ArrayAccess;
use JsonSerializable;
use Max\Database\Collection;
use Max\Database\Exceptions\ModelNotFoundException;
use Max\Database\Query;
use Max\Di\Exceptions\NotFoundException;
use Max\Utils\Arr;
use Max\Database\Eloquent\Traits\Relations;
use Max\Utils\Contracts\Arrayable;
use Max\Utils\Str;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;
use Swoole\Exception;
use Throwable;

/**
 * @method static Builder where(string $column, $value, string $operator = '=')
 * @method static Builder whereNull(string $column)
 * @method static Builder order(string $column, string $sort = 'ASC')
 */
abstract class Model implements ArrayAccess, Arrayable, JsonSerializable
{
    use Relations;

    /**
     * @var string
     */
    protected string $table;

    /**
     * @var ?string
     */
    protected ?string $connection = null;

    protected string $createdAt = 'created_at';
    protected string $updatedAt = 'updated_at';

    /**
     * @var string
     */
    protected string $key = 'id';

    /**
     * @var array
     */
    protected array $cast = [];

    /**
     * @var array
     */
    protected array $fillable = [];

    /**
     * @var array
     */
    protected array $original = [];

    /**
     * @var array
     */
    protected array $hidden = [];

    /**
     * @var array
     */
    protected array $attributes = [];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        if (!empty($attributes)) {
            $this->fill($attributes);
        }
        $this->table ??= Str::camel(class_basename(static::class));
    }

    /**
     * @return array
     */
    public function getFillable(): array
    {
        return $this->fillable;
    }

    /**
     * @param array $attributes
     *
     * @return array
     */
    protected function fillableFromArray(array $attributes)
    {
        if (count($this->getFillable()) > 0) {
            return array_intersect_key($attributes, array_flip($this->getFillable()));
        }
        return $attributes;
    }

    /**
     * @param array $attributes
     *
     * @return $this
     */
    public function fill(array $attributes): static
    {
        $this->original = $attributes;
        foreach ($this->fillableFromArray($attributes) as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }

    /**
     * @param       $id
     * @param array $columns
     *
     * @return Model|null
     */
    public static function find($id, array $columns = ['*']): ?static
    {
        try {
            return static::findOrFail($id, $columns);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param                $id
     * @param array|string[] $columns
     *
     * @return static
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws Throwable
     */
    public static function findOrFail($id, array $columns = ['*']): static
    {
        return static::query()->findOrFail($id, $columns);
    }

    /**
     * @param array $columns
     *
     * @return Model|null
     */
    public static function first(array $columns = ['*']): ?static
    {
        try {
            return static::firstOrFail($columns);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param array $columns
     *
     * @return static
     * @throws ModelNotFoundException
     */
    public static function firstOrFail(array $columns = ['*']): static
    {
        try {
            return static::query()->firstOrFail($columns) ?? throw new ModelNotFoundException('No data was found.');
        } catch (Throwable $throwable) {
            throw new ModelNotFoundException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @param array $columns
     *
     * @return Collection
     * @throws Exception
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws Throwable
     */
    public static function all(array $columns = ['*']): Collection
    {
        return static::query()->get($columns);
    }

    /**
     * @return Builder
     * @throws ReflectionException|NotFoundException|ContainerExceptionInterface
     */
    public static function query(): Builder
    {
        return (new static())->newQuery();
    }

    /**
     * @return Builder
     * @throws NotFoundException
     * @throws ReflectionException|ContainerExceptionInterface
     */
    public function newQuery(): Builder
    {
        /** @var Query $query */
        $query   = make(Query::class);
        $builder = new Builder($query->connection($this->connection));
        return $builder->from($this->table)->setModel($this);
    }

    /**
     * @return int
     * @throws Exception
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws Throwable
     */
    public function save(): int
    {
        $attributes = [];
        foreach ($this->getAttributes() as $key => $value) {
            if ($this->hasCast($key)) {
                $value = $this->cast($value, $this->getCast($key), true);
            }
            $attributes[$key] = $value;
        }
        return self::query()->insert($attributes);
    }

    /**
     * @param array $attributes
     *
     * @return static|null
     * @throws Exception
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws Throwable
     */
    public static function create(array $attributes): ?static
    {
        $lastInsertId = (new static($attributes))->save();

        return $lastInsertId ? static::find($lastInsertId) : null;
    }

    /**
     * @param $key
     *
     * @return bool
     */
    protected function hasCast($key): bool
    {
        return isset($this->cast[$key]);
    }

    /**
     * @return array
     */
    public function getOriginal(): array
    {
        return $this->original;
    }

    /**
     * @param      $value
     * @param      $cast
     * @param bool $isWrite
     *
     * @return mixed
     */
    protected function cast($value, $cast, bool $isWrite = false): mixed
    {
        return match ($cast) {
            'boolean', 'bool' => (bool)$value,
            'integer', 'int' => (int)$value,
            'string' => (string)$value,
            'double', 'float' => (float)$value,
            'json' => $isWrite ? json_encode($value, JSON_UNESCAPED_UNICODE) : json_decode($value, true),
            'serialize' => $isWrite ? serialize($value) : unserialize($value),
            default => $value,
        };
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getCast($key): mixed
    {
        return $this->cast[$key];
    }

    /**
     * @param $key
     * @param $value
     *
     * @return void
     */
    public function setAttribute($key, $value)
    {
        if ($this->hasCast($key)) {
            $value = $this->cast($value, $this->getCast($key));
        }
        $this->attributes[$key] = $value;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return Arr::except($this->getAttributes(), $this->getHidden());
    }

    /**
     * @return array
     */
    public function getHidden(): array
    {
        return $this->hidden;
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function hasAttribute($key): bool
    {
        return isset($this->attributes[$key]);
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getAttribute($key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param mixed $attributes
     *
     * @throws Exception
     */
    public function setAttributes(mixed $attributes): void
    {
        if ($attributes instanceof Arrayable) {
            $attributes = $attributes->toArray();
        }
        if (!is_array($attributes)) {
            throw new Exception('Cannot assign none array attributes to entity.');
        }
        $this->attributes = $attributes;
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
     * @param $key
     *
     * @return mixed|null
     */
    public function __get($key)
    {
        if ($this->hasAttribute($key)) {
            return $this->getAttribute($key);
        }
        if (method_exists($this, $key)) {
            $value = $this->{$key}();
            $this->setAttribute($key, $value);
            return $value;
        }

        return null;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return void
     */
    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     * @param $offset
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return $this->hasAttribute($offset);
    }

    /**
     * @param $offset
     *
     * @return mixed|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
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
        $this->original[$offset] = $value;
        $this->setAttribute($offset, $value);
    }

    /**
     * @param $offset
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        if ($this->hasAttribute($offset)) {
            unset($this->original[$offset], $this->attributes[$offset]);
        }
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     * @throws NotFoundException
     * @throws ReflectionException|ContainerExceptionInterface
     */
    public static function __callStatic(string $name, array $arguments)
    {
        return static::query()->{$name}(...$arguments);
    }
}
