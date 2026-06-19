<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Persistence\Approval;

use Yammi\Workflow\Application\DTO\ApprovalStepData;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowApprovalModel;

/**
 * @internal
 */
final class ApprovalStepMapper
{
    public static function fromModel(WorkflowApprovalModel $approval): ApprovalStepData
    {
        return new ApprovalStepData(
            $approval->step,
            $approval->label,
            $approval->assignee_type,
            $approval->assignee_id,
            $approval->status,
            $approval->decided_by_type,
            $approval->decided_by_id,
            $approval->comment,
            $approval->decided_at?->toIso8601String(),
        );
    }
}
