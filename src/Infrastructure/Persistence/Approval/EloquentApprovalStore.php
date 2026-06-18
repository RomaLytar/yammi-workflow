<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Persistence\Approval;

use Yammi\Workflow\Application\Contract\ApprovalStore;
use Yammi\Workflow\Application\DTO\ActorData;
use Yammi\Workflow\Application\DTO\ApprovalStepData;
use Yammi\Workflow\Domain\Approval\Enum\ApprovalStatus;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowApprovalModel;
use Yammi\Workflow\Infrastructure\Support\SubjectIdentity;

/**
 * @internal
 */
final class EloquentApprovalStore implements ApprovalStore
{
    public function replace(object $subject, array $labels): void
    {
        $type = SubjectIdentity::type($subject);
        $id = SubjectIdentity::id($subject);

        WorkflowApprovalModel::query()
            ->where('subject_type', $type)
            ->where('subject_id', $id)
            ->delete();

        $step = 1;

        foreach ($labels as $label) {
            WorkflowApprovalModel::create([
                'subject_type' => $type,
                'subject_id' => $id,
                'step' => $step,
                'label' => $label,
                'status' => ApprovalStatus::Pending->value,
            ]);

            $step++;
        }
    }

    public function steps(object $subject): array
    {
        return WorkflowApprovalModel::query()
            ->where('subject_type', SubjectIdentity::type($subject))
            ->where('subject_id', SubjectIdentity::id($subject))
            ->orderBy('step')
            ->get()
            ->map(static fn (WorkflowApprovalModel $approval): ApprovalStepData => new ApprovalStepData(
                $approval->step,
                $approval->label,
                $approval->status,
                $approval->decided_by_type,
                $approval->decided_by_id,
                $approval->comment,
                $approval->decided_at?->toIso8601String(),
            ))
            ->values()
            ->all();
    }

    public function decide(object $subject, int $step, ApprovalStatus $status, ?ActorData $actor, ?string $comment): void
    {
        WorkflowApprovalModel::query()
            ->where('subject_type', SubjectIdentity::type($subject))
            ->where('subject_id', SubjectIdentity::id($subject))
            ->where('step', $step)
            ->update([
                'status' => $status->value,
                'decided_by_type' => $actor?->type,
                'decided_by_id' => $actor?->id,
                'comment' => $comment,
                'decided_at' => now(),
            ]);
    }
}
