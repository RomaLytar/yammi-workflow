<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Persistence\Query;

use Yammi\Workflow\Application\Contract\WorkflowMetricsQuery;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowStateModel;

/**
 * @internal
 */
final class EloquentWorkflowMetrics implements WorkflowMetricsQuery
{
    public function stateDistribution(string $workflow): array
    {
        $states = (string) config('workflow.tables.states', 'workflow_states');
        $instances = (string) config('workflow.tables.instances', 'workflow_instances');
        $workflows = (string) config('workflow.tables.workflows', 'workflows');

        $rows = WorkflowStateModel::query()
            ->from("{$states} as ws")
            ->join("{$workflows} as w", 'w.id', '=', 'ws.workflow_id')
            ->leftJoin("{$instances} as wi", 'wi.state_id', '=', 'ws.id')
            ->where('w.key', $workflow)
            ->where('w.is_current', true)
            ->groupBy('ws.id', 'ws.key', 'ws.sort')
            ->orderBy('ws.sort')
            ->selectRaw('ws.key as state_key, COUNT(wi.id) as total')
            ->get();

        $distribution = [];

        foreach ($rows as $row) {
            $distribution[(string) $row->getAttribute('state_key')] = (int) $row->getAttribute('total');
        }

        return $distribution;
    }
}
