<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Support;

use Yammi\Workflow\Application\Contract\ApprovalNotifier;
use Yammi\Workflow\Application\DTO\ApprovalStepData;

final class RecordingApprovalNotifier implements ApprovalNotifier
{
    /**
     * @var list<array{subject: object, step: ApprovalStepData}>
     */
    public array $sent = [];

    public function assigned(object $subject, ApprovalStepData $step): void
    {
        $this->sent[] = ['subject' => $subject, 'step' => $step];
    }
}
