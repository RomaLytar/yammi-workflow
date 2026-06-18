<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\DTO;

use Yammi\Workflow\Domain\Workflow\WorkflowDefinition;

final class ResolvedWorkflow
{
    public function __construct(
        public readonly string $key,
        public readonly int $workflowId,
        public readonly WorkflowDefinition $definition,
    ) {}
}
