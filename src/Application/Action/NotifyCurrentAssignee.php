<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Action;

use Yammi\Workflow\Application\Contract\ApprovalNotifier;
use Yammi\Workflow\Application\DTO\ApprovalStepData;
use Yammi\Workflow\Application\DTO\ApprovalSummaryData;

final class NotifyCurrentAssignee
{
    public function __construct(
        private readonly ApprovalNotifier $notifier,
    ) {}

    /**
     * @param  list<ApprovalStepData>  $steps
     */
    public function __invoke(object $subject, array $steps): void
    {
        $current = ApprovalSummaryData::fromSteps($steps)->currentStep;

        foreach ($steps as $step) {
            if ($step->step === $current && $step->assigneeType !== null && $step->assigneeId !== null) {
                $this->notifier->assigned($subject, $step);

                return;
            }
        }
    }
}
