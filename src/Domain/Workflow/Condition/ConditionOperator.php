<?php

declare(strict_types=1);

namespace Yammi\Workflow\Domain\Workflow\Condition;

enum ConditionOperator: string
{
    case Equals = 'eq';
    case NotEquals = 'neq';
    case LessThan = 'lt';
    case LessOrEqual = 'lte';
    case GreaterThan = 'gt';
    case GreaterOrEqual = 'gte';
    case In = 'in';
    case NotEmpty = 'not_empty';
    case IsEmpty = 'empty';

    public function matches(mixed $actual, string $value): bool
    {
        return match ($this) {
            self::Equals => $this->scalar($actual) === $value,
            self::NotEquals => $this->scalar($actual) !== $value,
            self::LessThan => $this->number($actual) < (float) $value,
            self::LessOrEqual => $this->number($actual) <= (float) $value,
            self::GreaterThan => $this->number($actual) > (float) $value,
            self::GreaterOrEqual => $this->number($actual) >= (float) $value,
            self::In => in_array($this->scalar($actual), array_map('trim', explode(',', $value)), true),
            self::NotEmpty => $actual !== null && $actual !== '',
            self::IsEmpty => $actual === null || $actual === '',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Equals => 'equals',
            self::NotEquals => 'does not equal',
            self::LessThan => 'is less than',
            self::LessOrEqual => 'is at most',
            self::GreaterThan => 'is greater than',
            self::GreaterOrEqual => 'is at least',
            self::In => 'is one of',
            self::NotEmpty => 'is not empty',
            self::IsEmpty => 'is empty',
        };
    }

    private function scalar(mixed $actual): string
    {
        if (is_bool($actual)) {
            return $actual ? '1' : '0';
        }

        return is_scalar($actual) ? (string) $actual : '';
    }

    private function number(mixed $actual): float
    {
        return is_numeric($actual) ? (float) $actual : 0.0;
    }
}
