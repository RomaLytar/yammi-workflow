<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Persistence\Query;

use Yammi\Workflow\Application\Contract\TransitionHistory;
use Yammi\Workflow\Application\DTO\TransitionRecordData;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowTransitionModel;
use Yammi\Workflow\Infrastructure\Support\SubjectIdentity;

/**
 * @internal
 */
final class EloquentTransitionHistory implements TransitionHistory
{
    public function forSubject(object $subject): array
    {
        $transitions = (string) config('workflow.tables.transitions', 'workflow_transitions');
        $states = (string) config('workflow.tables.states', 'workflow_states');

        $rows = WorkflowTransitionModel::query()
            ->where('subject_type', SubjectIdentity::type($subject))
            ->where('subject_id', SubjectIdentity::id($subject))
            ->join("{$states} as wf_from", 'wf_from.id', '=', "{$transitions}.from_state_id")
            ->join("{$states} as wf_to", 'wf_to.id', '=', "{$transitions}.to_state_id")
            ->orderBy("{$transitions}.id")
            ->get([
                'wf_from.key as from_key',
                'wf_to.key as to_key',
                "{$transitions}.actor_type as actor_type",
                "{$transitions}.actor_id as actor_id",
                "{$transitions}.created_at as created_at",
            ]);

        return $rows->map(static fn (WorkflowTransitionModel $row): TransitionRecordData => new TransitionRecordData(
            (string) $row->getAttribute('from_key'),
            (string) $row->getAttribute('to_key'),
            $row->actor_type,
            $row->actor_id,
            (string) $row->getAttribute('created_at'),
        ))->values()->all();
    }
}
