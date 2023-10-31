<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Utils;

use Closure;
use Countable;
use Exception;
use Next\Utils\Contract\DeferringDisplayableValue;
use Next\Utils\Contract\Htmlable;
use Next\Utils\Proxy\HigherOrderTapProxy;
use RuntimeException;
use Throwable;

/**
 * Most of the methods in this file come from illuminate
 * thanks Laravel Team provide such a useful class.
 */

/**
 * Create a collection from the given value.
 */
function collect(mixed $value = null): Collection
{
    return new Collection($value);
}

/**
 * Fill in data where it's missing.
 */
function data_fill(mixed &$target, array|string $key, mixed $value): mixed
{
    return data_set($target, $key, $value, false);
}

/**
 * Get an item from an array or object using "dot" notation.
 */
function data_get(mixed $target, array|int|string|null $key, mixed $default = null): mixed
{
    if (is_null($key)) {
        return $target;
    }

    $key = is_array($key) ? $key : explode('.', $key);

    foreach ($key as $i => $segment) {
        unset($key[$i]);

        if (is_null($segment)) {
            return $target;
        }

        if ($segment === '*') {
            if ($target instanceof Collection) {
                $target = $target->all();
            } elseif (! is_array($target)) {
                return value($default);
            }

            $result = [];

            foreach ($target as $item) {
                $result[] = data_get($item, $key);
            }

            return in_array('*', $key) ? Arr::collapse($result) : $result;
        }

        if (Arr::accessible($target) && Arr::exists($target, $segment)) {
            $target = $target[$segment];
        } elseif (is_object($target) && isset($target->{$segment})) {
            $target = $target->{$segment};
        } else {
            return value($default);
        }
    }

    return $target;
}

/**
 * Set an item on an array or object using dot notation.
 */
function data_set(mixed &$target, array|string $key, mixed $value, bool $overwrite = true): mixed
{
    $segments = is_array($key) ? $key : explode('.', $key);

    if (($segment = array_shift($segments)) === '*') {
        if (! Arr::accessible($target)) {
            $target = [];
        }

        if ($segments) {
            foreach ($target as &$inner) {
                data_set($inner, $segments, $value, $overwrite);
            }
        } elseif ($overwrite) {
            foreach ($target as &$inner) {
                $inner = $value;
            }
        }
    } elseif (Arr::accessible($target)) {
        if ($segments) {
            if (! Arr::exists($target, $segment)) {
                $target[$segment] = [];
            }

            data_set($target[$segment], $segments, $value, $overwrite);
        } elseif ($overwrite || ! Arr::exists($target, $segment)) {
            $target[$segment] = $value;
        }
    } elseif (is_object($target)) {
        if ($segments) {
            if (! isset($target->{$segment})) {
                $target->{$segment} = [];
            }

            data_set($target->{$segment}, $segments, $value, $overwrite);
        } elseif ($overwrite || ! isset($target->{$segment})) {
            $target->{$segment} = $value;
        }
    } else {
        $target = [];

        if ($segments) {
            data_set($target[$segment], $segments, $value, $overwrite);
        } elseif ($overwrite) {
            $target[$segment] = $value;
        }
    }

    return $target;
}

/**
 * Get the first element of an array. Useful for method chaining.
 */
function head(array $array): mixed
{
    return reset($array);
}

/**
 * Get the last element from an array.
 */
function last(array $array): mixed
{
    return end($array);
}

/**
 * Get a new stringable object from the given string.
 *
 * @param null|string $string
 *
 * @return mixed|Stringable
 */
function str($string = null)
{
    if (func_num_args() === 0) {
        return new class() {
            public function __call($method, $parameters)
            {
                return Str::$method(...$parameters);
            }

            public function __toString()
            {
                return '';
            }
        };
    }

    return Str::of($string);
}

/**
 * Return the default value of the given value.
 */
function value(mixed $value, ...$args): mixed
{
    return $value instanceof Closure ? $value(...$args) : $value;
}

/**
 * Call the given Closure with the given value then return the value.
 */
function tap(mixed $value, callable $callback = null): mixed
{
    if (is_null($callback)) {
        return new HigherOrderTapProxy($value);
    }

    $callback($value);

    return $value;
}

/**
 * Assign high numeric IDs to a config item to force appending.
 */
function append_config(array $array): array
{
    $start = 9999;

    foreach ($array as $key => $value) {
        if (is_numeric($key)) {
            ++$start;

            $array[$start] = Arr::pull($array, $key);
        }
    }

    return $array;
}

/**
 * Determine if the given value is "blank".
 */
function blank(mixed $value): bool
{
    if (is_null($value)) {
        return true;
    }

    if (is_string($value)) {
        return trim($value) === '';
    }

    if (is_numeric($value) || is_bool($value)) {
        return false;
    }

    if ($value instanceof Countable) {
        return count($value) === 0;
    }

    return empty($value);
}

/**
 * Get the class "basename" of the given object / class.
 */
function class_basename(object|string $class): string
{
    $class = is_object($class) ? get_class($class) : $class;

    return basename(str_replace('\\', '/', $class));
}

/**
 * Returns all traits used by a class, its parent classes and trait of their traits.
 */
function class_uses_recursive(object|string $class): array
{
    if (is_object($class)) {
        $class = get_class($class);
    }

    $results = [];

    foreach (array_reverse(class_parents($class)) + [$class => $class] as $class) {
        $results += trait_uses_recursive($class);
    }

    return array_unique($results);
}

/**
 * Encode HTML special characters in a string.
 */
function e(Htmlable|string|DeferringDisplayableValue|null $value, bool $doubleEncode = true): string
{
    if ($value instanceof DeferringDisplayableValue) {
        $value = $value->resolveDisplayableValue();
    }

    if ($value instanceof Htmlable) {
        return $value->toHtml();
    }

    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8', $doubleEncode);
}

/**
 * Determine if a value is "filled".
 */
function filled(mixed $value): bool
{
    return ! blank($value);
}

/**
 * Get an item from an object using "dot" notation.
 */
function object_get(object $object, ?string $key, mixed $default = null): mixed
{
    if (is_null($key) || trim($key) === '') {
        return $object;
    }

    foreach (explode('.', $key) as $segment) {
        if (! is_object($object) || ! isset($object->{$segment})) {
            return value($default);
        }

        $object = $object->{$segment};
    }

    return $object;
}

/**
 * Provide access to optional objects.
 */
function optional(mixed $value = null, callable $callback = null): mixed
{
    if (is_null($callback)) {
        return new Optional($value);
    }
    if (! is_null($value)) {
        return $callback($value);
    }
}

/**
 * Retry an operation a given number of times.
 *
 * @throws Exception
 */
function retry(int $times, callable $callback, int|Closure $sleepMilliseconds = 0, callable $when = null): mixed
{
    $attempts = 0;

    beginning:
    $attempts++;
    --$times;

    try {
        return $callback($attempts);
    } catch (Exception $e) {
        if ($times < 1 || ($when && ! $when($e))) {
            throw $e;
        }

        if ($sleepMilliseconds) {
            usleep(value($sleepMilliseconds, $attempts) * 1000);
        }

        goto beginning;
    }
}

/**
 * Throw the given exception if the given condition is true.
 *
 * @throws Throwable
 */
function throw_if(mixed $condition, Throwable|string $exception = 'RuntimeException', ...$parameters): mixed
{
    if ($condition) {
        if (is_string($exception) && class_exists($exception)) {
            $exception = new $exception(...$parameters);
        }

        throw is_string($exception) ? new RuntimeException($exception) : $exception;
    }

    return $condition;
}

/**
 * Throw the given exception unless the given condition is true.
 *
 * @throws Throwable
 */
function throw_unless(mixed $condition, Throwable|string $exception = 'RuntimeException', ...$parameters): mixed
{
    throw_if(! $condition, $exception, ...$parameters);

    return $condition;
}

/**
 * Returns all traits used by a trait and its traits.
 */
function trait_uses_recursive(string $trait): array
{
    $traits = class_uses($trait) ?: [];

    foreach ($traits as $trait) {
        $traits += trait_uses_recursive($trait);
    }

    return $traits;
}

/**
 * Transform the given value if it is present.
 */
function transform(mixed $value, callable $callback, mixed $default = null): mixed
{
    if (filled($value)) {
        return $callback($value);
    }

    if (is_callable($default)) {
        return $default($value);
    }

    return $default;
}

/**
 * Determine whether the current environment is Windows based.
 */
function windows_os(): bool
{
    return PHP_OS_FAMILY === 'Windows';
}

/**
 * Return the given value, optionally passed through the given callback.
 */
function with(mixed $value, callable $callback = null): mixed
{
    return is_null($callback) ? $value : $callback($value);
}

function is_valid_ip(string $ip, string $type = ''): bool
{
    $flag = match (strtolower($type)) {
        'ipv4'  => FILTER_FLAG_IPV4,
        'ipv6'  => FILTER_FLAG_IPV6,
        default => 0,
    };

    return boolval(filter_var($ip, FILTER_VALIDATE_IP, $flag));
}

function ip2bin(string $ip): string
{
    if (is_valid_ip($ip, 'ipv6')) {
        $IPHex = str_split(bin2hex(inet_pton($ip)), 4);
        foreach ($IPHex as $key => $value) {
            $IPHex[$key] = intval($value, 16);
        }
        $IPBin = vsprintf('%016b%016b%016b%016b%016b%016b%016b%016b', $IPHex);
    } else {
        $IPHex = str_split(bin2hex(inet_pton($ip)), 2);
        foreach ($IPHex as $key => $value) {
            $IPHex[$key] = intval($value, 16);
        }
        $IPBin = vsprintf('%08b%08b%08b%08b', $IPHex);
    }

    return $IPBin;
}
