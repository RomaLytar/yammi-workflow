<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Action;

use Yammi\Workflow\Application\Contract\ApprovalStore;

final class RequestApprovalAction
{
    public function __construct(
        private readonly ApprovalStore $approvals,
    ) {}

    /**
     * @param  list<string>  $steps
     */
    public function __invoke(object $subject, array $steps): void
    {
        $this->approvals->replace($subject, $steps);
    }
}
