<?php

declare(strict_types=1);

namespace Yammi\Workflow\Domain\Workflow\Repository;

use Yammi\Workflow\Domain\Workflow\WorkflowDefinition;

interface WorkflowDefinitionRepository
{
    public function find(string $key): WorkflowDefinition;

    public function findByWorkflowId(int $workflowId): WorkflowDefinition;

    public function currentWorkflowId(string $key): int;
}
