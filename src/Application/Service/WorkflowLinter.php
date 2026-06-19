<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Service;

use Yammi\Workflow\Domain\Workflow\Analysis\WorkflowAnalysis;
use Yammi\Workflow\Domain\Workflow\Analysis\WorkflowAnalyzer;
use Yammi\Workflow\Domain\Workflow\Repository\WorkflowDefinitionRepository;

final class WorkflowLinter
{
    public function __construct(
        private readonly WorkflowDefinitionRepository $definitions,
        private readonly WorkflowAnalyzer $analyzer,
    ) {}

    public function analyze(string $key): WorkflowAnalysis
    {
        return $this->analyzer->analyze($this->definitions->find($key));
    }
}
