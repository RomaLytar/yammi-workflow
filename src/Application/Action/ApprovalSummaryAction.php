<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Action;

use Yammi\Workflow\Application\Contract\ApprovalStore;
use Yammi\Workflow\Application\DTO\ApprovalSummaryData;

final class ApprovalSummaryAction
{
    public function __construct(
        private readonly ApprovalStore $approvals,
    ) {}

    public function __invoke(object $subject): ApprovalSummaryData
    {
        return ApprovalSummaryData::fromSteps($this->approvals->steps($subject));
    }
}
