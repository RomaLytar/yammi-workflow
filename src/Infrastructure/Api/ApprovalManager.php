<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Api;

use Illuminate\Contracts\Auth\Authenticatable;
use Yammi\Workflow\Application\Action\ApprovalSummaryAction;
use Yammi\Workflow\Application\Action\ApproveStepAction;
use Yammi\Workflow\Application\Action\RejectStepAction;
use Yammi\Workflow\Application\Action\RequestApprovalAction;
use Yammi\Workflow\Application\Contract\ApprovalInbox;
use Yammi\Workflow\Application\DTO\ActorData;
use Yammi\Workflow\Application\DTO\ApprovalStepData;
use Yammi\Workflow\Application\DTO\ApprovalStepInput;
use Yammi\Workflow\Application\DTO\ApprovalSummaryData;

final class ApprovalManager
{
    public function __construct(
        private readonly RequestApprovalAction $request,
        private readonly ApproveStepAction $approve,
        private readonly RejectStepAction $reject,
        private readonly ApprovalSummaryAction $summary,
        private readonly ApprovalInbox $inbox,
    ) {}

    /**
     * @param  list<string>|array<string, Authenticatable|null>  $steps  labels, or label => assignee
     */
    public function request(object $subject, array $steps): void
    {
        ($this->request)($subject, $this->normalize($steps));
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

    /**
     * The current pending approval steps assigned to the given user.
     *
     * @return list<ApprovalStepData>
     */
    public function pendingFor(Authenticatable $user): array
    {
        return $this->inbox->assignedTo($user::class, (string) $user->getAuthIdentifier());
    }

    /**
     * @param  list<string>|array<string, Authenticatable|null>  $steps
     * @return list<ApprovalStepInput>
     */
    private function normalize(array $steps): array
    {
        $inputs = [];

        foreach ($steps as $label => $assignee) {
            $inputs[] = is_string($assignee)
                ? new ApprovalStepInput($assignee)
                : new ApprovalStepInput((string) $label, $this->actor($assignee));
        }

        return $inputs;
    }

    private function actor(?Authenticatable $user): ?ActorData
    {
        return $user === null ? null : new ActorData($user::class, (string) $user->getAuthIdentifier());
    }
}
