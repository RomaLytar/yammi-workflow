<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Persistence\Approval;

use Illuminate\Database\Query\Builder;
use Yammi\Workflow\Application\Contract\ApprovalInbox;
use Yammi\Workflow\Domain\Approval\Enum\ApprovalStatus;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowApprovalModel;

/**
 * @internal
 */
final class EloquentApprovalInbox implements ApprovalInbox
{
    public function assignedTo(string $assigneeType, string $assigneeId): array
    {
        $table = (string) config('workflow.tables.approvals', 'workflow_approvals');

        return WorkflowApprovalModel::query()
            ->where('assignee_type', $assigneeType)
            ->where('assignee_id', $assigneeId)
            ->where('status', ApprovalStatus::Pending->value)
            ->whereNotExists(function (Builder $earlier) use ($table): void {
                $earlier->selectRaw('1')
                    ->from("{$table} as earlier")
                    ->whereColumn('earlier.subject_type', "{$table}.subject_type")
                    ->whereColumn('earlier.subject_id', "{$table}.subject_id")
                    ->whereColumn('earlier.step', '<', "{$table}.step")
                    ->where('earlier.status', '!=', ApprovalStatus::Approved->value);
            })
            ->orderBy('subject_type')
            ->orderBy('subject_id')
            ->get()
            ->map(ApprovalStepMapper::fromModel(...))
            ->values()
            ->all();
    }
}
