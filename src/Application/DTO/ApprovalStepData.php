<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\DTO;

use Yammi\Workflow\Domain\Approval\Enum\ApprovalStatus;

final class ApprovalStepData
{
    public function __construct(
        public readonly int $step,
        public readonly string $label,
        public readonly ?string $assigneeType,
        public readonly ?string $assigneeId,
        public readonly ApprovalStatus $status,
        public readonly ?string $decidedByType,
        public readonly ?string $decidedById,
        public readonly ?string $comment,
        public readonly ?string $decidedAt,
    ) {}
}
