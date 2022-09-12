<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Database\Eloquent;

use ArrayAccess;
use JsonSerializable;
use Max\Database\Collection;
use Max\Database\Exception\ModelNotFoundException;
use Max\Database\Manager;
use Max\Utils\Arr;
use Max\Utils\Contract\Arrayable;
use Max\Utils\Str;
use RuntimeException;
use Throwable;

use function Max\Utils\class_basename;

abstract class Model implements ArrayAccess, Arrayable, JsonSerializable
{
    protected static string $table;

    protected static string $connection = '';

    protected static string $primaryKey = 'id';

    protected static array $cast = [];

    protected static array $fillable = [];

    protected static array $hidden = [];

    protected static Manager $manager;

    protected array $original = [];

    protected array $attributes = [];

    protected array $appends = [];

    public function __construct(array $attributes = [])
    {
        if (!empty($attributes)) {
            $this->fill($attributes);
        }
        $this->table ??= Str::camel(class_basename(static::class));
    }

    /**
     * @param $key
     *
     * @return null|mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        return static::query()->{$name}(...$arguments);
    }

    /**
     * @return $this
     */
    public function fill(array $attributes): static
    {
        $this->original = $attributes;
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }

    /**
     * @param $id
     *
     * @return null|Model
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
     * @param $id
     *
     * @return Model
     * @throws ModelNotFoundException
     */
    public static function findOrFail($id, array $columns = ['*']): static
    {
        return static::query()->findOrFail($id, $columns);
    }

    /**
     * @return null|Model
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
     * @throws ModelNotFoundException
     */
    public static function firstOrFail(array $columns = ['*']): static
    {
        try {
            return static::query()->firstOrFail($columns) ?? throw new ModelNotFoundException('No data was found.');
        } catch (Throwable $e) {
            throw new ModelNotFoundException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public static function all(array $columns = ['*']): Collection
    {
        return static::query()->get($columns);
    }

    public function save(): int
    {
        $attributes = [];
        foreach ($this->getAttributes() as $key => $value) {
            if ($this->hasCast($key)) {
                $value = $this->cast($value, $this->getCast($key), true);
            }
            $attributes[$key] = $value;
        }

        return static::query()->insert($attributes);
    }

    public static function create(array $attributes): ?static
    {
        $lastInsertId = (new static($attributes))->save();

        return $lastInsertId ? static::find($lastInsertId) : null;
    }

    public function getPrimaryKey(): string
    {
        return static::$primaryKey;
    }

    public function getTable(): string
    {
        return static::$table;
    }

    public static function query(): Builder
    {
        return (new static())->newQuery();
    }

    public static function setManager(Manager $manager)
    {
        static::$manager = $manager;
    }

    public function newQuery(): Builder
    {
        try {
            $builder = new Builder(static::$manager->query(static::$connection));
            return $builder->from($this->getTable())->setModel($this);
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getOriginal(): array
    {
        return $this->original;
    }

    /**
     * @param $key
     */
    public function getCast($key): mixed
    {
        return static::$cast[$key];
    }

    /**
     * @param $key
     * @param $value
     */
    public function setAttribute($key, $value): void
    {
        $this->original[$key] = $value;

        if (in_array($key, $this->getFillable())) {
            if ($this->hasCast($key)) {
                $value = $this->cast($value, $this->getCast($key));
            }
            $this->attributes[$key] = $value;
        }
    }

    public function toArray(): array
    {
        return Arr::except($this->getAttributes(), $this->getHidden());
    }

    public function getHidden(): array
    {
        return static::$hidden;
    }

    public function hasAttribute($key): bool
    {
        return isset($this->attributes[$key]);
    }

    /**
     * @param $key
     */
    public function getAttribute($key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setAttributes(mixed $attributes): void
    {
        if ($attributes instanceof Arrayable) {
            $attributes = $attributes->toArray();
        }
        if (!is_array($attributes)) {
            throw new \InvalidArgumentException('Cannot assign none array attributes to entity.');
        }
        $this->attributes = $attributes;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function offsetExists($offset): bool
    {
        return $this->hasAttribute($offset);
    }

    public function offsetGet($offset): mixed
    {
        return $this->getAttribute($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->setAttribute($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        if ($this->hasAttribute($offset)) {
            unset($this->original[$offset], $this->attributes[$offset]);
        }
    }

    public function getFillable(): array
    {
        return static::$fillable;
    }

    protected function fillableFromArray(array $attributes): array
    {
        $fillable = $this->getFillable();
        if (count($fillable) > 0) {
            return array_intersect_key($attributes, array_flip($fillable));
        }
        return $attributes;
    }

    /**
     * @param $key
     */
    protected function hasCast($key): bool
    {
        return isset($this->cast[$key]);
    }

    /**
     * @param $value
     * @param $cast
     */
    protected function cast($value, $cast, bool $isWrite = false): mixed
    {
        return match ($cast) {
            'boolean', 'bool' => (bool)$value,
            'integer', 'int' => (int)$value,
            'string' => (string)$value,
            'double' => (float)$value,
            'float' => (float)$value,
            'json' => $isWrite ? json_encode($value, JSON_UNESCAPED_UNICODE) : json_decode($value, true),
            'serialize' => $isWrite ? serialize($value) : unserialize($value),
            default => $value,
        };
    }
}
