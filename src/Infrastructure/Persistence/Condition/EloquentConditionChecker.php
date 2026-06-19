<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Persistence\Condition;

use Illuminate\Database\Eloquent\Model;
use Yammi\Workflow\Application\Contract\TransitionConditionChecker;
use Yammi\Workflow\Domain\Workflow\Condition\Condition;
use Yammi\Workflow\Domain\Workflow\Condition\ConditionOperator;
use Yammi\Workflow\Domain\Workflow\Condition\ConditionSet;
use Yammi\Workflow\Infrastructure\Persistence\Support\TransitionDefLocator;

/**
 * @internal
 */
final class EloquentConditionChecker implements TransitionConditionChecker
{
    public function satisfied(int $workflowId, string $from, string $to, object $subject): bool
    {
        $rules = TransitionDefLocator::find($workflowId, $from, $to)?->conditions ?? [];

        if ($rules === []) {
            return true;
        }

        $conditions = array_map(
            static fn (array $rule): Condition => new Condition(
                $rule['field'],
                ConditionOperator::from($rule['operator']),
                (string) $rule['value'],
            ),
            $rules,
        );

        return (new ConditionSet($conditions))->satisfiedBy($this->attributes($subject));
    }

    /**
     * @return array<string, mixed>
     */
    private function attributes(object $subject): array
    {
        return $subject instanceof Model ? $subject->attributesToArray() : get_object_vars($subject);
    }
}
