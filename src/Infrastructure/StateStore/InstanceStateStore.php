<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\StateStore;

use Yammi\Workflow\Application\Contract\StateStore;
use Yammi\Workflow\Domain\Workflow\ValueObject\State;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowInstanceModel;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowStateModel;
use Yammi\Workflow\Infrastructure\Support\SubjectIdentity;

/**
 * @internal
 */
final class InstanceStateStore implements StateStore
{
    public function current(object $subject): ?State
    {
        $instance = $this->instance($subject);

        if ($instance === null) {
            return null;
        }

        $state = WorkflowStateModel::query()->whereKey($instance->state_id)->first();

        return $state === null ? null : new State($state->key);
    }

    public function put(object $subject, State $state, int $workflowId): void
    {
        $row = WorkflowStateModel::query()
            ->where('workflow_id', $workflowId)
            ->where('key', $state->name)
            ->firstOrFail();

        WorkflowInstanceModel::query()->updateOrCreate(
            [
                'subject_type' => SubjectIdentity::type($subject),
                'subject_id' => SubjectIdentity::id($subject),
            ],
            [
                'workflow_id' => $workflowId,
                'state_id' => $row->id,
            ],
        );
    }

    public function pinnedWorkflowId(object $subject): ?int
    {
        return $this->instance($subject)?->workflow_id;
    }

    private function instance(object $subject): ?WorkflowInstanceModel
    {
        return WorkflowInstanceModel::query()
            ->where('subject_type', SubjectIdentity::type($subject))
            ->where('subject_id', SubjectIdentity::id($subject))
            ->first();
    }
}
