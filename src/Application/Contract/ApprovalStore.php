<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Contract;

use Yammi\Workflow\Application\DTO\ActorData;
use Yammi\Workflow\Application\DTO\ApprovalStepData;
use Yammi\Workflow\Domain\Approval\Enum\ApprovalStatus;

interface ApprovalStore
{
    /**
     * @param  list<string>  $labels
     */
    public function replace(object $subject, array $labels): void;

    /**
     * @return list<ApprovalStepData>
     */
    public function steps(object $subject): array;

    public function decide(object $subject, int $step, ApprovalStatus $status, ?ActorData $actor, ?string $comment): void;
}
