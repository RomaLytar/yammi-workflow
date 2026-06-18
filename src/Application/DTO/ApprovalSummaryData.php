<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\DTO;

use Yammi\Workflow\Domain\Approval\Enum\ApprovalStatus;

final class ApprovalSummaryData
{
    /**
     * @param  list<ApprovalStepData>  $steps
     */
    public function __construct(
        public readonly array $steps,
        public readonly ?int $currentStep,
        public readonly bool $isApproved,
        public readonly bool $isRejected,
    ) {}

    /**
     * @param  list<ApprovalStepData>  $steps
     */
    public static function fromSteps(array $steps): self
    {
        $rejected = false;
        $current = null;
        $allApproved = $steps !== [];

        foreach ($steps as $step) {
            if ($step->status === ApprovalStatus::Rejected) {
                $rejected = true;
            }

            if ($step->status !== ApprovalStatus::Approved) {
                $allApproved = false;
            }

            if ($current === null && $step->status === ApprovalStatus::Pending) {
                $current = $step->step;
            }
        }

        return new self(
            array_values($steps),
            $rejected ? null : $current,
            $allApproved && ! $rejected,
            $rejected,
        );
    }
}
