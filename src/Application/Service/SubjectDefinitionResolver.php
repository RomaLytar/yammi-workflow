<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Service;

use Yammi\Workflow\Application\Contract\StateStore;
use Yammi\Workflow\Application\Contract\WorkflowKeyResolver;
use Yammi\Workflow\Application\DTO\ResolvedWorkflow;
use Yammi\Workflow\Domain\Workflow\Repository\WorkflowDefinitionRepository;

final class SubjectDefinitionResolver
{
    public function __construct(
        private readonly WorkflowDefinitionRepository $definitions,
        private readonly StateStore $states,
        private readonly WorkflowKeyResolver $keys,
    ) {}

    public function resolve(object $subject): ResolvedWorkflow
    {
        $key = $this->keys->keyFor($subject);
        $workflowId = $this->states->pinnedWorkflowId($subject) ?? $this->definitions->currentWorkflowId($key);

        return new ResolvedWorkflow(
            $key,
            $workflowId,
            $this->definitions->findByWorkflowId($workflowId),
        );
    }
}
