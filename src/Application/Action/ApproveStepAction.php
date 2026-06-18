<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Action;

use Yammi\Workflow\Application\Contract\ActorResolver;
use Yammi\Workflow\Application\Contract\ApprovalStore;
use Yammi\Workflow\Application\DTO\ApprovalSummaryData;
use Yammi\Workflow\Domain\Approval\Enum\ApprovalStatus;
use Yammi\Workflow\Domain\Approval\Exception\NoPendingApprovalException;
use Yammi\Workflow\Infrastructure\Support\SubjectIdentity;

final class ApproveStepAction
{
    public function __construct(
        private readonly ApprovalStore $approvals,
        private readonly ActorResolver $actors,
    ) {}

    public function __invoke(object $subject, ?string $comment = null): ApprovalSummaryData
    {
        $summary = ApprovalSummaryData::fromSteps($this->approvals->steps($subject));

        if ($summary->currentStep === null) {
            throw NoPendingApprovalException::forSubject(SubjectIdentity::type($subject));
        }

        $this->approvals->decide($subject, $summary->currentStep, ApprovalStatus::Approved, $this->actors->resolve(), $comment);

        return ApprovalSummaryData::fromSteps($this->approvals->steps($subject));
    }
}
