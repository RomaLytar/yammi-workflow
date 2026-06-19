<?php

declare(strict_types=1);

namespace Yammi\Workflow\Domain\Workflow\Condition;

final class ConditionSet
{
    /**
     * @param  list<Condition>  $conditions
     */
    public function __construct(
        private readonly array $conditions,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function satisfiedBy(array $attributes): bool
    {
        foreach ($this->conditions as $condition) {
            if (! $condition->matches($attributes[$condition->field] ?? null)) {
                return false;
            }
        }

        return true;
    }

    public function isEmpty(): bool
    {
        return $this->conditions === [];
    }
}
