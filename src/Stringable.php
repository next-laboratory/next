<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Utils;

use Closure;
use JsonSerializable;
use Next\Utils\Traits\Conditionable;
use Next\Utils\Traits\Tappable;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Most of the methods in this file come from illuminate
 * thanks Laravel Team provide such a useful class.
 */
class Stringable implements JsonSerializable, \Stringable
{
    use Conditionable;
    use Macroable;
    use Tappable;

    /**
     * Create a new instance of the class.
     *
     * @param string $value the underlying string value
     */
    public function __construct(
        protected string $value = ''
    )
    {
    }

    /**
     * Proxy dynamic properties onto methods.
     */
    public function __get(string $key)
    {
        return $this->{$key}();
    }

    /**
     * Get the raw string value.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }

    /**
     * Return the remainder of a string after the first occurrence of a given value.
     */
    public function after(string $search): static
    {
        return new static(Str::after($this->value, $search));
    }

    /**
     * Return the remainder of a string after the last occurrence of a given value.
     */
    public function afterLast(string $search): static
    {
        return new static(Str::afterLast($this->value, $search));
    }

    /**
     * Append the given values to the string.
     */
    public function append(string ...$values): static
    {
        return new static($this->value . implode('', $values));
    }

    /**
     * Transliterate a UTF-8 value to ASCII.
     */
    public function ascii(string $language = 'en'): static
    {
        return new static(Str::ascii($this->value, $language));
    }

    /**
     * Get the trailing name component of the path.
     */
    public function basename(string $suffix = ''): static
    {
        return new static(basename($this->value, $suffix));
    }

    /**
     * Get the basename of the class path.
     */
    public function classBasename(): static
    {
        return new static(class_basename($this->value));
    }

    /**
     * Get the portion of a string before the first occurrence of a given value.
     */
    public function before(string $search): static
    {
        return new static(Str::before($this->value, $search));
    }

    /**
     * Get the portion of a string before the last occurrence of a given value.
     */
    public function beforeLast(string $search): static
    {
        return new static(Str::beforeLast($this->value, $search));
    }

    /**
     * Get the portion of a string between two given values.
     */
    public function between(string $from, string $to): static
    {
        return new static(Str::between($this->value, $from, $to));
    }

    /**
     * Convert a value to camel case.
     */
    public function camel(): static
    {
        return new static(Str::camel($this->value));
    }

    /**
     * Determine if a given string contains a given substring.
     */
    public function contains(array|string $needles): bool
    {
        return Str::contains($this->value, $needles);
    }

    /**
     * Determine if a given string contains all array values.
     */
    public function containsAll(array $needles): bool
    {
        return Str::containsAll($this->value, $needles);
    }

    /**
     * Get the parent directory's path.
     */
    public function dirname(int $levels = 1): static
    {
        return new static(dirname($this->value, $levels));
    }

    /**
     * Determine if a given string ends with a given substring.
     */
    public function endsWith(array|string $needles): bool
    {
        return Str::endsWith($this->value, $needles);
    }

    /**
     * Determine if the string is an exact match with the given value.
     */
    public function exactly(string $value): bool
    {
        return $this->value === $value;
    }

    /**
     * Explode the string into an array.
     */
    public function explode(string $delimiter, int $limit = PHP_INT_MAX): Collection
    {
        return collect(explode($delimiter, $this->value, $limit));
    }

    /**
     * Split a string using a regular expression or by length.
     */
    public function split(int|string $pattern, int $limit = -1, int $flags = 0): Collection
    {
        if (filter_var($pattern, FILTER_VALIDATE_INT) !== false) {
            return collect(mb_str_split($this->value, $pattern));
        }

        $segments = preg_split($pattern, $this->value, $limit, $flags);

        return !empty($segments) ? collect($segments) : collect();
    }

    /**
     * Cap a string with a single instance of a given value.
     */
    public function finish(string $cap): static
    {
        return new static(Str::finish($this->value, $cap));
    }

    /**
     * Determine if a given string matches a given pattern.
     */
    public function is(array|string $pattern): bool
    {
        return Str::is($pattern, $this->value);
    }

    /**
     * Determine if a given string is 7 bit ASCII.
     */
    public function isAscii(): bool
    {
        return Str::isAscii($this->value);
    }

    /**
     * Determine if the given string is empty.
     */
    public function isEmpty(): bool
    {
        return $this->value === '';
    }

    /**
     * Determine if the given string is not empty.
     */
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    /**
     * Convert a string to kebab case.
     */
    public function kebab(): static
    {
        return new static(Str::kebab($this->value));
    }

    /**
     * Return the length of the given string.
     */
    public function length(?string $encoding = null): int
    {
        return Str::length($this->value, $encoding);
    }

    /**
     * Limit the number of characters in a string.
     */
    public function limit(int $limit = 100, string $end = '...'): static
    {
        return new static(Str::limit($this->value, $limit, $end));
    }

    /**
     * Convert the given string to lower-case.
     */
    public function lower(): static
    {
        return new static(Str::lower($this->value));
    }

    /**
     * Convert GitHub flavored Markdown into HTML.
     */
    public function markdown(array $options = []): static
    {
        return new static(Str::markdown($this->value, $options));
    }

    /**
     * Get the string matching the given pattern.
     */
    public function match(string $pattern): static
    {
        return new static(Str::match($pattern, $this->value));
    }

    /**
     * Get the string matching the given pattern.
     */
    public function matchAll(string $pattern): Collection
    {
        return Str::matchAll($pattern, $this->value);
    }

    /**
     * Determine if the string matches the given pattern.
     */
    public function test(string $pattern): bool
    {
        return $this->match($pattern)->isNotEmpty();
    }

    /**
     * Pad both sides of the string with another.
     */
    public function padBoth(int $length, string $pad = ' '): static
    {
        return new static(Str::padBoth($this->value, $length, $pad));
    }

    /**
     * Pad the left side of the string with another.
     */
    public function padLeft(int $length, string $pad = ' '): static
    {
        return new static(Str::padLeft($this->value, $length, $pad));
    }

    /**
     * Pad the right side of the string with another.
     */
    public function padRight(int $length, string $pad = ' '): static
    {
        return new static(Str::padRight($this->value, $length, $pad));
    }

    /**
     * Parse a Class@method style callback into class and method.
     */
    public function parseCallback(?string $default = null): array
    {
        return Str::parseCallback($this->value, $default);
    }

    /**
     * Call the given callback and return a new string.
     */
    public function pipe(callable $callback): static
    {
        return new static(call_user_func($callback, $this));
    }

    /**
     * Get the plural form of an English word.
     */
    public function plural(int $count = 2): static
    {
        return new static(Str::plural($this->value, $count));
    }

    /**
     * Pluralize the last word of an English, studly caps case string.
     */
    public function pluralStudly(int $count = 2): static
    {
        return new static(Str::pluralStudly($this->value, $count));
    }

    /**
     * Prepend the given values to the string.
     */
    public function prepend(string ...$values): static
    {
        return new static(implode('', $values) . $this->value);
    }

    /**
     * Remove any occurrence of the given string in the subject.
     *
     * @param array<string>|string $search
     */
    public function remove(array|string $search, bool $caseSensitive = true): static
    {
        return new static(Str::remove($search, $this->value, $caseSensitive));
    }

    /**
     * Repeat the string.
     */
    public function repeat(int $times): static
    {
        return new static(Str::repeat($this->value, $times));
    }

    /**
     * Replace the given value in the given string.
     *
     * @param string|string[] $search
     * @param string|string[] $replace
     */
    public function replace(array|string $search, array|string $replace): static
    {
        return new static(Str::replace($search, $replace, $this->value));
    }

    /**
     * Replace a given value in the string sequentially with an array.
     */
    public function replaceArray(string $search, array $replace): static
    {
        return new static(Str::replaceArray($search, $replace, $this->value));
    }

    /**
     * Replace the first occurrence of a given value in the string.
     *
     * @param string $search
     * @param string $replace
     *
     * @return static
     */
    public function replaceFirst($search, $replace)
    {
        return new static(Str::replaceFirst($search, $replace, $this->value));
    }

    /**
     * Replace the last occurrence of a given value in the string.
     *
     * @param string $search
     * @param string $replace
     *
     * @return static
     */
    public function replaceLast($search, $replace)
    {
        return new static(Str::replaceLast($search, $replace, $this->value));
    }

    /**
     * Replace the patterns matching the given regular expression.
     *
     * @param string         $pattern
     * @param Closure|string $replace
     * @param int            $limit
     *
     * @return static
     */
    public function replaceMatches($pattern, $replace, $limit = -1)
    {
        if ($replace instanceof Closure) {
            return new static(preg_replace_callback($pattern, $replace, $this->value, $limit));
        }

        return new static(preg_replace($pattern, $replace, $this->value, $limit));
    }

    /**
     * Begin a string with a single instance of a given value.
     *
     * @param string $prefix
     *
     * @return static
     */
    public function start($prefix)
    {
        return new static(Str::start($this->value, $prefix));
    }

    /**
     * Convert the given string to upper-case.
     *
     * @return static
     */
    public function upper()
    {
        return new static(Str::upper($this->value));
    }

    /**
     * Convert the given string to title case.
     *
     * @return static
     */
    public function title()
    {
        return new static(Str::title($this->value));
    }

    /**
     * Get the singular form of an English word.
     *
     * @return static
     */
    public function singular()
    {
        return new static(Str::singular($this->value));
    }

    /**
     * Generate a URL friendly "slug" from a given string.
     *
     * @param string      $separator
     * @param null|string $language
     *
     * @return static
     */
    public function slug($separator = '-', $language = 'en')
    {
        return new static(Str::slug($this->value, $separator, $language));
    }

    /**
     * Convert a string to snake case.
     *
     * @param string $delimiter
     *
     * @return static
     */
    public function snake($delimiter = '_')
    {
        return new static(Str::snake($this->value, $delimiter));
    }

    /**
     * Determine if a given string starts with a given substring.
     *
     * @param array|string $needles
     *
     * @return bool
     */
    public function startsWith($needles)
    {
        return Str::startsWith($this->value, $needles);
    }

    /**
     * Convert a value to studly caps case.
     *
     * @return static
     */
    public function studly()
    {
        return new static(Str::studly($this->value));
    }

    /**
     * Returns the portion of the string specified by the start and length parameters.
     *
     * @param int      $start
     * @param null|int $length
     *
     * @return static
     */
    public function substr($start, $length = null)
    {
        return new static(Str::substr($this->value, $start, $length));
    }

    /**
     * Returns the number of substring occurrences.
     *
     * @param string   $needle
     * @param null|int $offset
     * @param null|int $length
     *
     * @return int
     */
    public function substrCount($needle, $offset = null, $length = null)
    {
        return Str::substrCount($this->value, $needle, $offset ?? 0, $length);
    }

    /**
     * Trim the string of the given characters.
     *
     * @param string $characters
     *
     * @return static
     */
    public function trim($characters = null)
    {
        return new static(trim(...array_merge([$this->value], func_get_args())));
    }

    /**
     * Left trim the string of the given characters.
     *
     * @param string $characters
     *
     * @return static
     */
    public function ltrim($characters = null)
    {
        return new static(ltrim(...array_merge([$this->value], func_get_args())));
    }

    /**
     * Right trim the string of the given characters.
     *
     * @param string $characters
     *
     * @return static
     */
    public function rtrim($characters = null)
    {
        return new static(rtrim(...array_merge([$this->value], func_get_args())));
    }

    /**
     * Make a string's first character uppercase.
     *
     * @return static
     */
    public function ucfirst()
    {
        return new static(Str::ucfirst($this->value));
    }

    /**
     * Execute the given callback if the string is empty.
     *
     * @param callable $callback
     *
     * @return static
     */
    public function whenEmpty($callback)
    {
        if ($this->isEmpty()) {
            $result = $callback($this);

            return is_null($result) ? $this : $result;
        }

        return $this;
    }

    /**
     * Execute the given callback if the string is not empty.
     *
     * @param callable $callback
     *
     * @return static
     */
    public function whenNotEmpty($callback)
    {
        if ($this->isNotEmpty()) {
            $result = $callback($this);

            return is_null($result) ? $this : $result;
        }

        return $this;
    }

    /**
     * Limit the number of words in a string.
     *
     * @param int    $words
     * @param string $end
     *
     * @return static
     */
    public function words($words = 100, $end = '...')
    {
        return new static(Str::words($this->value, $words, $end));
    }

    /**
     * Get the number of words a string contains.
     *
     * @return int
     */
    public function wordCount()
    {
        return str_word_count($this->value);
    }

    /**
     * Dump the string.
     *
     * @return $this
     */
    public function dump()
    {
        VarDumper::dump($this->value);

        return $this;
    }

    /**
     * Dump the string and end the script.
     */
    public function dd()
    {
        $this->dump();

        exit(1);
    }

    /**
     * Convert the object to a string when JSON encoded.
     *
     * @return string
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->__toString();
    }
}
