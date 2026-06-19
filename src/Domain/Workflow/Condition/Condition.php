<?php

declare(strict_types=1);

namespace Yammi\Workflow\Domain\Workflow\Condition;

final class Condition
{
    public function __construct(
        public readonly string $field,
        public readonly ConditionOperator $operator,
        public readonly string $value,
    ) {}

    public function matches(mixed $actual): bool
    {
        return $this->operator->matches($actual, $this->value);
    }
}
