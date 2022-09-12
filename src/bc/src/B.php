<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Bc;

use Stringable;

class B implements Stringable
{
    protected string $value;

    public function __construct(
        mixed $value,
        protected ?int $scale = null
    ) {
        $this->value = (string)$value;
    }

    public function add(mixed $value, ?int $scale = null): static
    {
        $scale ??= $this->scale;
        return new static(bcadd($this->value, (string)$value, $scale), $scale);
    }

    public function sub(mixed $value, ?int $scale = null): static
    {
        $scale ??= $this->scale;
        return new static(bcsub($this->value, (string)$value, $scale), $scale);
    }

    public function div(mixed $value, ?int $scale = null): static
    {
        $scale ??= $this->scale;
        return new static(bcdiv($this->value, (string)$value, $scale), $scale);
    }

    public function mod(mixed $value, ?int $scale = null): static
    {
        $scale ??= $this->scale;
        return new static(bcmod($this->value, (string)$value, $scale), $scale);
    }

    public function mul(mixed $value, ?int $scale = null): static
    {
        $scale ??= $this->scale;
        return new static(bcmul($this->value, (string)$value, $scale), $scale);
    }

    public function pow(string $exponent, ?int $scale = null): static
    {
        $scale ??= $this->scale;
        return new static(bcpow($this->value, $exponent, $scale), $scale);
    }

    public function comp(mixed $value, ?int $scale = null): int
    {
        return bccomp((string)$value, $this->value, $scale ??= $this->scale);
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

    public function __toString(): string
    {
        return $this->value;
    }

    public function string(): string
    {
        return $this->__toString();
    }

    public function int(): int
    {
        return (int)$this->value;
    }

    public function float(): float
    {
        return (float)$this->value;
    }
}
