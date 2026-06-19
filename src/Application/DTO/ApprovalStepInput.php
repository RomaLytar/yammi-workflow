<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\DTO;

final class ApprovalStepInput
{
    public function __construct(
        public readonly string $label,
        public readonly ?ActorData $assignee = null,
    ) {}
}
