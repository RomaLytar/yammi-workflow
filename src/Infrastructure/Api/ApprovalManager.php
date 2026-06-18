<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Api;

use Yammi\Workflow\Application\Action\ApprovalSummaryAction;
use Yammi\Workflow\Application\Action\ApproveStepAction;
use Yammi\Workflow\Application\Action\RejectStepAction;
use Yammi\Workflow\Application\Action\RequestApprovalAction;
use Yammi\Workflow\Application\DTO\ApprovalSummaryData;

final class ApprovalManager
{
    public function __construct(
        private readonly RequestApprovalAction $request,
        private readonly ApproveStepAction $approve,
        private readonly RejectStepAction $reject,
        private readonly ApprovalSummaryAction $summary,
    ) {}

    /**
     * @param  list<string>  $steps
     */
    public function request(object $subject, array $steps): void
    {
        ($this->request)($subject, $steps);
    }

    public function approve(object $subject, ?string $comment = null): ApprovalSummaryData
    {
        return ($this->approve)($subject, $comment);
    }

    public function reject(object $subject, ?string $comment = null): ApprovalSummaryData
    {
        return ($this->reject)($subject, $comment);
    }

    public function for(object $subject): ApprovalSummaryData
    {
        return ($this->summary)($subject);
    }
}
