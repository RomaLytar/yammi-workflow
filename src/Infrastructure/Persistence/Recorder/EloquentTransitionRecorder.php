<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Persistence\Recorder;

use Yammi\Workflow\Application\Contract\TransitionRecorder;
use Yammi\Workflow\Application\DTO\ActorData;
use Yammi\Workflow\Domain\Workflow\ValueObject\State;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowModel;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowStateModel;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowTransitionModel;
use Yammi\Workflow\Infrastructure\Support\SubjectIdentity;

/**
 * @internal
 */
final class EloquentTransitionRecorder implements TransitionRecorder
{
    public function record(object $subject, string $workflow, State $from, State $to, ?ActorData $actor): void
    {
        $workflowRow = WorkflowModel::query()->where('key', $workflow)->firstOrFail();

        WorkflowTransitionModel::create([
            'subject_type' => SubjectIdentity::type($subject),
            'subject_id' => SubjectIdentity::id($subject),
            'workflow_id' => $workflowRow->id,
            'from_state_id' => $this->stateId($workflowRow->id, $from->name),
            'to_state_id' => $this->stateId($workflowRow->id, $to->name),
            'actor_type' => $actor?->type,
            'actor_id' => $actor?->id,
        ]);
    }

    private function stateId(int $workflowId, string $key): int
    {
        return (int) WorkflowStateModel::query()
            ->where('workflow_id', $workflowId)
            ->where('key', $key)
            ->value('id');
    }
}
