<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Persistence\Approval;

use Yammi\Workflow\Application\Contract\ApprovalStore;
use Yammi\Workflow\Application\Contract\TransitionApprovalGate;
use Yammi\Workflow\Application\DTO\ApprovalSummaryData;
use Yammi\Workflow\Domain\Approval\Exception\ApprovalRequiredException;
use Yammi\Workflow\Infrastructure\Persistence\Support\TransitionDefLocator;

/**
 * @internal
 */
final class EloquentApprovalGate implements TransitionApprovalGate
{
    public function __construct(
        private readonly ApprovalStore $approvals,
    ) {}

    public function check(int $workflowId, string $from, string $to, object $subject): void
    {
        $steps = TransitionDefLocator::find($workflowId, $from, $to)?->approval ?? [];

        if ($steps === []) {
            return;
        }

        $summary = ApprovalSummaryData::fromSteps($this->approvals->steps($subject));

        if ($summary->isApproved) {
            return;
        }

        if ($summary->steps === []) {
            $this->approvals->replace($subject, $steps);
        }

        throw ApprovalRequiredException::forTransition($from, $to);
    }
}
