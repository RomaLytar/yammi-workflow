<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Persistence\Condition;

use Illuminate\Database\Eloquent\Model;
use Yammi\Workflow\Application\Contract\TransitionConditionChecker;
use Yammi\Workflow\Domain\Workflow\Condition\Condition;
use Yammi\Workflow\Domain\Workflow\Condition\ConditionOperator;
use Yammi\Workflow\Domain\Workflow\Condition\ConditionSet;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowStateModel;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowTransitionDefModel;

/**
 * @internal
 */
final class EloquentConditionChecker implements TransitionConditionChecker
{
    public function satisfied(int $workflowId, string $from, string $to, object $subject): bool
    {
        $fromId = $this->stateId($workflowId, $from);
        $toId = $this->stateId($workflowId, $to);

        if ($fromId === null || $toId === null) {
            return true;
        }

        $definition = WorkflowTransitionDefModel::query()
            ->where('workflow_id', $workflowId)
            ->where('from_state_id', $fromId)
            ->where('to_state_id', $toId)
            ->first();

        $rules = $definition?->conditions ?? [];

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

    private function stateId(int $workflowId, string $key): ?int
    {
        $id = WorkflowStateModel::query()
            ->where('workflow_id', $workflowId)
            ->where('key', $key)
            ->value('id');

        return $id === null ? null : (int) $id;
    }
}
