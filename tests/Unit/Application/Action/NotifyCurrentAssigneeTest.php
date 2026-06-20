<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Unit\Application\Action;

use PHPUnit\Framework\TestCase;
use Yammi\Workflow\Application\Action\NotifyCurrentAssignee;
use Yammi\Workflow\Application\DTO\ApprovalStepData;
use Yammi\Workflow\Domain\Approval\Enum\ApprovalStatus;
use Yammi\Workflow\Tests\Support\RecordingApprovalNotifier;

final class NotifyCurrentAssigneeTest extends TestCase
{
    private function step(int $number, ApprovalStatus $status, ?string $assigneeId = null): ApprovalStepData
    {
        return new ApprovalStepData(
            $number,
            'step-'.$number,
            $assigneeId === null ? null : 'App\\Models\\User',
            $assigneeId,
            $status,
            null,
            null,
            null,
            null,
        );
    }

    public function test_it_notifies_the_assignee_of_the_current_step(): void
    {
        $notifier = new RecordingApprovalNotifier;
        $subject = (object) [];

        (new NotifyCurrentAssignee($notifier))($subject, [
            $this->step(1, ApprovalStatus::Pending, '7'),
            $this->step(2, ApprovalStatus::Pending, '8'),
        ]);

        $this->assertCount(1, $notifier->sent);
        $this->assertSame(1, $notifier->sent[0]['step']->step);
        $this->assertSame($subject, $notifier->sent[0]['subject']);
    }

    public function test_it_does_nothing_when_the_current_step_has_no_assignee(): void
    {
        $notifier = new RecordingApprovalNotifier;

        (new NotifyCurrentAssignee($notifier))((object) [], [$this->step(1, ApprovalStatus::Pending)]);

        $this->assertSame([], $notifier->sent);
    }

    public function test_it_does_nothing_when_no_step_is_pending(): void
    {
        $notifier = new RecordingApprovalNotifier;

        (new NotifyCurrentAssignee($notifier))((object) [], [$this->step(1, ApprovalStatus::Approved, '7')]);

        $this->assertSame([], $notifier->sent);
    }
}
