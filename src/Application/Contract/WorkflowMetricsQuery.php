<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Contract;

interface WorkflowMetricsQuery
{
    /**
     * @return array<string, int>
     */
    public function stateDistribution(string $workflow): array;
}
