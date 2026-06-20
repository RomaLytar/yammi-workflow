<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Notification;

use Illuminate\Support\Facades\Notification;
use Yammi\Workflow\Application\Contract\ApprovalNotifier;
use Yammi\Workflow\Application\DTO\ApprovalStepData;
use Yammi\Workflow\Infrastructure\Presentation\SubjectPresenter;
use Yammi\Workflow\Infrastructure\Support\SubjectIdentity;

/**
 * @internal
 */
final class LaravelApprovalNotifier implements ApprovalNotifier
{
    public function assigned(object $subject, ApprovalStepData $step): void
    {
        if ($step->assigneeType === null || $step->assigneeId === null) {
            return;
        }

        $assignee = SubjectPresenter::resolve($step->assigneeType, $step->assigneeId);

        if ($assignee === null || ! method_exists($assignee, 'notify')) {
            return;
        }

        Notification::send($assignee, new ApprovalAssignedNotification(
            SubjectPresenter::title(SubjectIdentity::type($subject), SubjectIdentity::id($subject)),
            $step->step,
            $step->label,
        ));
    }
}
