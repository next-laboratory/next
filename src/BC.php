<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Utils;

class BC implements \Stringable
{
    protected string $value;

    public function __construct(
        mixed $value,
        protected ?int $scale = null
    ) {
        $this->value = (string) $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function new(mixed $value, ?int $scale = null): static
    {
        return new static($value, $scale);
    }

    /**
     * @param BC|string|\Stringable $value
     */
    public function add(mixed $value, ?int $scale = null): static
    {
        $scale ??= $this->scale;
        return new static(\bcadd($this->value, (string) $value, $scale), $scale);
    }

    /**
     * @param BC|string|\Stringable $value
     */
    public function sub(mixed $value, ?int $scale = null): static
    {
        $scale ??= $this->scale;
        return new static(\bcsub($this->value, (string) $value, $scale), $scale);
    }

    /**
     * @param BC|string|\Stringable $value
     */
    public function div(mixed $value, ?int $scale = null): static
    {
        $scale ??= $this->scale;
        return new static(\bcdiv($this->value, (string) $value, $scale), $scale);
    }

    /**
     * @param BC|string|\Stringable $value
     */
    public function mod(mixed $value, ?int $scale = null): static
    {
        $scale ??= $this->scale;
        return new static(\bcmod($this->value, (string) $value, $scale), $scale);
    }

    /**
     * @param BC|string|\Stringable $value
     */
    public function mul(mixed $value, ?int $scale = null): static
    {
        $scale ??= $this->scale;
        return new static(\bcmul($this->value, (string) $value, $scale), $scale);
    }

    public function pow(string $exponent, ?int $scale = null): static
    {
        $scale ??= $this->scale;
        return new static(\bcpow($this->value, $exponent, $scale), $scale);
    }

    public function sqrt(?int $scale = null): static
    {
        return new static(\bcsqrt($this->value, $scale ?? $this->scale));
    }

    /**
     * @param BC|string|\Stringable $value
     */
    public function comp(mixed $value, ?int $scale = null): int
    {
        return \bccomp($this->value, (string) $value, $scale ?? $this->scale);
    }

    public function gt(mixed $value, ?int $scale = null): bool
    {
        return $this->comp($value, $scale ?? $this->scale) > 0;
    }

    public function lt(mixed $value, ?int $scale = null): bool
    {
        return $this->comp($value, $scale ?? $this->scale) < 0;
    }

    public function eq(mixed $value, ?int $scale = null): bool
    {
        return $this->comp($value, $scale ?? $this->scale) === 0;
    }

    public function toString(): string
    {
        return $this->__toString();
    }

    public function getValue(): string
    {
        return $this->toString();
    }

    public function toPrecision(int $precision = 0)
    {
        throw new \BadMethodCallException('Not implemented');
    }

    public function toInt(): int
    {
        return (int) $this->value;
    }

    public function toFloat(): float
    {
        return (float) $this->value;
    }

    /**
     * 转为指数形式.
     */
    public function toExponential()
    {
        throw new \BadMethodCallException('Not implemented');
    }
}
