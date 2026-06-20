<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Action;

use Yammi\Workflow\Application\Contract\ApprovalStore;
use Yammi\Workflow\Application\DTO\ApprovalStepInput;

final class RequestApprovalAction
{
    public function __construct(
        private readonly ApprovalStore $approvals,
        private readonly NotifyCurrentAssignee $notify,
    ) {}

    /**
     * @param  list<ApprovalStepInput>  $steps
     */
    public function __invoke(object $subject, array $steps): void
    {
        $this->approvals->replace($subject, $steps);

        ($this->notify)($subject, $this->approvals->steps($subject));
    }
}
