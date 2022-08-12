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
use Max\Database\Eloquent\Traits\Relations;
use Max\Database\Exception\ModelNotFoundException;
use Max\Database\Manager;
use Max\Database\Query\Expression;
use Max\Utils\Arr;
use Max\Utils\Contract\Arrayable;
use Max\Utils\Str;
use Throwable;

/**
 * @method static Builder where(string $column, $value, string $operator = '=')
 * @method static Builder whereNull(string $column)
 * @method static Builder order(Expression|string $column, string $sort = 'ASC')
 * @method static Builder limit(int $limit)
 */
abstract class Model implements ArrayAccess, Arrayable, JsonSerializable
{
    use Relations;

    protected string $table;

    /**
     * @var ?string
     */
    protected ?string $connection = null;

    protected string $createdAt = 'created_at';

    protected string $updatedAt = 'updated_at';

    protected string $key = 'id';

    protected array $cast = [];

    protected array $fillable = [];

    protected array $original = [];

    protected array $hidden = [];

    protected array $appends = [];

    protected array $attributes = [];

    public function __construct(array $attributes = [])
    {
        if (! empty($attributes)) {
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

    public function getFillable(): array
    {
        return $this->fillable;
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
        } catch (Throwable $throwable) {
            throw new ModelNotFoundException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public static function all(array $columns = ['*']): Collection
    {
        return static::query()->get($columns);
    }

    public static function query(): Builder
    {
        return (new static())->newQuery();
    }

    public function newQuery(): Builder
    {
        try {
            /** @var Manager $query */
            $query   = make(Manager::class);
            $builder = new Builder($query->connection($this->connection));
            return $builder->from($this->table)->setModel($this);
        } catch (Throwable $throwable) {
            throw new \RuntimeException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }
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
        return self::query()->insert($attributes);
    }

    public static function create(array $attributes): ?static
    {
        $lastInsertId = (new static($attributes))->save();

        return $lastInsertId ? static::find($lastInsertId) : null;
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
        return $this->cast[$key];
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
        return $this->hidden;
    }

    /**
     * @param $key
     */
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
        if (! is_array($attributes)) {
            throw new \InvalidArgumentException('Cannot assign none array attributes to entity.');
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
     * @return null|mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }

    /**
     * @param $offset
     * @param $value
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->original[$offset] = $value;
        $this->setAttribute($offset, $value);
    }

    /**
     * @param $offset
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        if ($this->hasAttribute($offset)) {
            unset($this->original[$offset], $this->attributes[$offset]);
        }
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
            'boolean', 'bool' => (bool) $value,
            'integer', 'int' => (int) $value,
            'string' => (string) $value,
            'double', 'float' => (float) $value,
            'json'      => $isWrite ? json_encode($value, JSON_UNESCAPED_UNICODE) : json_decode($value, true),
            'serialize' => $isWrite ? serialize($value) : unserialize($value),
            default     => $value,
        };
    }
}
