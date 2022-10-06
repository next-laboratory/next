<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Validation;

use InvalidArgumentException;

use function explode;

class Validator
{
    use Rules;

    protected Error $error;

    protected array $valid = [];

    public function __construct(
        protected array $data,
        protected array $rules,
        protected array $messages = [],
        protected bool $stopOnFirstFailure = true,
    ) {
    }

    public function validated(): array
    {
        return $this->validate()->valid();
    }

    public function validate(): static
    {
        return $this->passes();
    }

    /**
     * @throws ValidationException
     */
    public function passes(): static
    {
        $this->error = new Error();
        foreach ($this->rules as $key => $rules) {
            if (! is_string($rules)) {
                throw new InvalidArgumentException('The rule must be a string like \'required|length:1,2\'');
            }
            $value = $this->getData($key);
            foreach (explode('|', $rules) as $rule) {
                [$ruleName, $ruleParams] = RuleParser::parse($rule);
                if (! method_exists($this, $ruleName)) {
                    throw new ValidationException('Rule \'' . $ruleName . '\' is not exist');
                }
                try {
                    $this->{$ruleName}($key, $value, ...$ruleParams);
                    $this->valid[$key] = $value;
                } catch (ValidationException $e) {
                    if ($this->stopOnFirstFailure) {
                        throw $e;
                    }
                    $this->error->push($e->getMessage());
                }
            }
        }
        return $this;
    }

    public function errors(): Error
    {
        return $this->error;
    }

    public function valid(): array
    {
        return $this->valid;
    }

    public function fails(): bool
    {
        return ! $this->error->isEmpty();
    }

    public function failed(): array
    {
        return $this->error->all();
    }

    protected function getData(string $key = ''): mixed
    {
        return $key ? ($this->data[$key] ?? null) : $this->data;
    }

    protected function getMessage(string $key, string $default = '验证失败'): string
    {
        return $this->messages[$key] ?? $default;
    }
}
