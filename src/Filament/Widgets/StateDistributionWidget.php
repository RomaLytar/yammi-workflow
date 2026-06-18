<?php

declare(strict_types=1);

namespace Yammi\Workflow\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowStateModel;

class StateDistributionWidget extends StatsOverviewWidget
{
    protected static ?string $heading = 'Instances by state';

    protected function getStats(): array
    {
        $states = (string) config('workflow.tables.states', 'workflow_states');
        $instances = (string) config('workflow.tables.instances', 'workflow_instances');

        $rows = WorkflowStateModel::query()
            ->from("{$states} as ws")
            ->join("{$instances} as wi", 'wi.state_id', '=', 'ws.id')
            ->groupBy('ws.key', 'ws.sort')
            ->orderBy('ws.sort')
            ->selectRaw('ws.key as state_key, COUNT(wi.id) as total')
            ->get();

        if ($rows->isEmpty()) {
            return [Stat::make('Instances', '0')];
        }

        return $rows
            ->map(static fn (WorkflowStateModel $row): Stat => Stat::make(
                ucfirst((string) $row->getAttribute('state_key')),
                (string) $row->getAttribute('total'),
            ))
            ->all();
    }
}
